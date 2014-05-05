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

		if (!$this->check_token())
		{
			$this->display_error('メンバー登録: 不正なURL');
			return;
		}

		$form_member_profile = new Form_MemberProfile('regist');
		$add_fields = array();
		$add_fields['password'] = Form_Util::get_model_field('member_auth', 'password');
		$add_fields['token']    = Form_Util::get_model_field('member_pre', 'token');
		$form_member_profile->set_validation($add_fields);
		$form_member_profile->set_validation_message('match_value', ':labelが正しくありません。');

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
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
				// 仮登録情報の削除
				$email    = $member_pre->email;
				$password = $member_pre->password;
				$member_pre->delete();

				// member_profile 登録
				$form_member_profile->set_member_obj($auth->get_member());
				$form_member_profile->seve();

				// timeline 投稿
				if (Module::loaded('timeline')) \Timeline\Site_Model::save_timeline($member_id, null, 'member_register', $member_id);
				DB::commit_transaction();

				$maildata = array();
				$maildata['from_name']    = \Config::get('mail.member_setting_common.from_name');
				$maildata['from_address'] = \Config::get('mail.member_setting_common.from_mail_address');
				$maildata['subject']      = \Config::get('mail.member_register_mail.subject');
				$maildata['to_address']   = $member_pre->email;
				$maildata['to_name']      = $member_pre->name;
				$maildata['password']     = $member_pre->password;
				$this->send_register_mail($maildata);

				if ($auth->login($email, $password))
				{
					Session::set_flash('message', '登録が完了しました。');
					Response::redirect('member');
				}
				Session::set_flash('error', 'ログインに失敗しました');
				Response::redirect(Config::get('site.login_uri.site'));
			}
			catch(EmailValidationFailedException $e)
			{
				$this->display_error('メンバー登録: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
				return;
			}
			catch(EmailSendingFailedException $e)
			{
				$this->display_error('メンバー登録: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
				return;
			}
			catch(\Auth\SimpleUserUpdateException $e)
			{
				if (DB::in_transaction()) DB::rollback_transaction();
				Session::set_flash('error', 'そのアドレスは登録できません');
			}
			catch(FuelException $e)
			{
				if (DB::in_transaction()) DB::rollback_transaction();
				Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(term(array('member.view', 'site.registration', 'form.confirm')), array('member/signup' => term('site.signup')));
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

		if (!$form = Fieldset::instance('confirm_signup'))
		{
			$form = $this->get_form_signup();
		}
		$val = $form->validation();

		if ($val->run())
		{
			$post = $val->validated();

			$redirect_uri = Config::get('site.login_uri.site');
			$message = '仮登録が完了しました。受信したメール内に記載された URL より本登録を完了してください。';
			try
			{
				if (Model_MemberAuth::get4email($post['email']))
				{
					if (conf('member.register.email.hideUniqueCheck'))
					{
						Session::set_flash('message', $message);
						Response::redirect($redirect_uri);
					}
					throw new FuelException('その'.term('site.email').'は登録できません。');
				}
				$data = array();
				//$data['name'] = $post['name'];
				DB::start_transaction();
				$token = $this->save_member_pre($post['email'], $post['password']);
				DB::commit_transaction();

				$maildata = array();
				$maildata['from_name']    = \Config::get('mail.member_setting_common.from_name');
				$maildata['from_address'] = \Config::get('mail.member_setting_common.from_mail_address');
				$maildata['subject']      = \Config::get('mail.member_confirm_signup_mail.subject');
				$maildata['to_address']   = $post['email'];
				//$maildata['to_name']      = $post['name'];
				$maildata['password']     = $post['password'];
				$maildata['token']        = $token;
				$this->send_confirm_signup_mail($maildata);

				Session::set_flash('message', $message);
				Response::redirect($redirect_uri);
			}
			catch(EmailValidationFailedException $e)
			{
				$this->display_error(term(array('member.view', 'site.registration')).': 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
			}
			catch(EmailSendingFailedException $e)
			{
				$this->display_error(term(array('member.view', 'site.registration')).': 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
			}
			catch(FuelException $e)
			{
				if (DB::in_transaction()) DB::rollback_transaction();
				Session::set_flash('error', $e->getMessage());
				$this->action_signup();
			}
		}
		else
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_signup();
		}
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

	private function save_member_pre($email, $password)
	{
		$member_pre = Model_MemberPre::forge();
		$member_pre->email = $email;
		$member_pre->password = $password;
		$member_pre->token = Util_toolkit::create_hash();
		$member_pre->save();

		return $member_pre->token;
	}

	private function send_register_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$data['body'] = <<< END
メンバー登録が完了しました。

====================
お名前: {$data['to_name']}
メールアドレス: {$data['to_address']}
====================

END;

		Util_toolkit::sendmail($data);
	}

	private function send_confirm_signup_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$register_url = sprintf('%s?token=%s', Uri::create('member/register'), $data['token']);
		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
{$site_name} の仮登録が完了しました。
まだ登録は完了しておりません。

以下のアドレスをクリックすることにより、{$site_name} アカウントの登録確認が完了します。
{$register_url}

上記の確認作業が完了しないと、{$site_name} のサービスが利用できません。

END;

		Util_toolkit::sendmail($data);
	}
}
