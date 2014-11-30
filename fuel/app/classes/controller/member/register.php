<?php

class Controller_Member_Register extends Controller_Site
{
	//public $template = 'admin/template';

	protected $check_not_auth_action = array(
		'index',
		'signup',
		'confirm_signup',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Execute index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		if (!$member_pre = $this->check_token())
		{
			$this->display_error('メンバー登録: 不正なURL');
			return;
		}

		$form_member_profile = new Form_MemberProfile('regist');
		$add_fields = array();
		$add_fields['password'] = Form_Util::get_model_field('member_auth', 'password');
		$add_fields['token']    = Form_Util::get_model_field('member_pre', 'token');
		$form_member_profile->set_validation($add_fields, 'member_register');
		$form_member_profile->set_validation_message('match_value', ':labelが正しくありません。');

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$error_message = '';
			$is_transaction_rollback = false;
			try
			{
				$form_member_profile->validate();
				DB::start_transaction();
				$post = $form_member_profile->get_validated_values();

				// create new member
				$auth = Auth::instance();
				if (!$member_id = $auth->create_user($member_pre->email, $member_pre->password, $post['member_name']))
				{
					throw new FuelException('create member error.');
				}
				$member = $auth->get_member();
				// 仮登録情報の削除
				$email    = $member_pre->email;
				$password = $member_pre->password;
				$member_pre->delete();

				// member_profile 登録
				$form_member_profile->set_member_obj($member);
				$form_member_profile->seve();

				// timeline 投稿
				if (Module::loaded('timeline')) \Timeline\Site_Model::save_timeline($member_id, null, 'member_register', $member_id, $member->created_at);
				DB::commit_transaction();

				$mail = new Site_Mail('memberRegister');
				$mail->send($member_pre->email, array('to_name' => $member->name));

				if ($auth->login($email, $password))
				{
					Session::set_flash('message', '登録が完了しました。');
					Response::redirect('member');
				}
				Session::set_flash('error', 'ログインに失敗しました');
				Response::redirect(conf('login_uri.site'));
			}
			catch(EmailValidationFailedException $e)
			{
				Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
				$error_message = 'メール送信エラー';
			}
			catch(EmailSendingFailedException $e)
			{
				Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
				$error_message = 'メール送信エラー';
			}
			catch(\Auth\SimpleUserUpdateException $e)
			{
				$is_transaction_rollback = true;
				$error_message = 'そのアドレスは登録できません';
			}
			catch(\Database_Exception $e)
			{
				$is_transaction_rollback = true;
				$error_message = \Util_Db::get_db_error_message($e);
			}
			catch(FuelException $e)
			{
				$is_transaction_rollback = true;
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
				Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(term('member.view', 'site.registration', 'form.confirm'), array('member/signup' => term('site.signup')));
		$this->template->content = View::forge('member/register/index', array(
			'val' => $form_member_profile->get_validation(),
			'member_public_flags' => $form_member_profile->get_member_public_flags(),
			'profiles' => $form_member_profile->get_profiles(),
			'member_profile_public_flags' => $form_member_profile->get_member_profile_public_flags(),
			'member_pre' => $member_pre,
		));
	}

	/**
	 * Mmeber signup
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_signup()
	{
		if (!$form = Fieldset::instance('confirm_signup'))
		{
			$form = $this->get_form_signup();
		}

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs(term('site.signup'));
		$this->template->content = View::forge('member/register/signup', array('form' => $form));
		$this->template->content->set_safe('html_form', $form->build('member/register/confirm_signup'));// form の action に入る
	}

	/**
	 * Execute confirm signup
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm_signup()
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();

		if (!$form = Fieldset::instance('confirm_signup')) $form = $this->get_form_signup();
		$val = $form->validation();

		$redirect_uri = conf('login_uri.site');
		$success_message = '仮登録が完了しました。受信したメール内に記載された URL より本登録を完了してください。';
		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			if (!$val->run()) throw new \FuelException($val->show_errors());
			$post = $val->validated();

			if (Model_MemberAuth::get4email($post['email']))
			{
				if (conf('member.register.email.hideUniqueCheck'))
				{
					Session::set_flash('message', $success_message);
					Response::redirect($redirect_uri);
				}
				throw new FuelException('その'.term('site.email').'は登録できません。');
			}

			DB::start_transaction();
			$token = Model_MemberPre::save_with_token($post['email'], $post['password']);
			DB::commit_transaction();

			$mail = new Site_Mail('memberSignup');
			$mail->send($post['email'], array(
				'register_url' => sprintf('%s?token=%s', Uri::create('member/register'), $token),
			));

			Session::set_flash('message', $success_message);
			Response::redirect($redirect_uri);
		}
		catch(EmailValidationFailedException $e)
		{
			Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
			$error_message = 'メール送信エラー';
		}
		catch(EmailSendingFailedException $e)
		{
			Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
			$error_message = 'メール送信エラー';
		}
		catch(\Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = \Util_Db::get_db_error_message($e);
		}
		catch(FuelException $e)
		{
			$is_transaction_rollback = true;
			$error_message = $e->getMessage();
		}

		if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
		Session::set_flash('error', $error_message);
		$this->action_signup();
	}

	public function get_form_signup()
	{
		return Site_Util::get_form_instance('confirm_signup', Model_MemberAuth::forge(), true, null, 'submit');
	}

	private function check_token()
	{
		if (!$member_pre = Model_MemberPre::get4token(Input::param('token'))) return false;
		if (Site_Util::check_token_lifetime($member_pre->created_at, term('member.register.token_lifetime'))) return false;

		return $member_pre;
	}
}
