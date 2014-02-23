<?php

class Controller_Member_Register extends Controller_Site
{
	//public $template = 'admin/template';

	protected $check_not_auth_action = array(
		'index',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Execute register
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		if (!$member_pre = Model_MemberPre::get4token(Input::param('token')))
		{
			$this->display_error('メンバー登録: 不正なURL');
			return;
		}

		$form_member_profile = new Form_MemberProfile('regist');
		$add_fields = array();
		$add_fields['password'] = Form_Util::get_model_field('member_auth', 'password');
		$add_fields['token']    = Form_Util::get_model_field('member_pre', 'token');
		$form_member_profile->set_validation(true, $add_fields);
		$form_member_profile->set_validation_message('match_value', ':labelが正しくありません。');

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			try
			{
				$form_member_profile->validate_public_flag();
				if (!$form_member_profile->validate()) throw new FuelException($form_member_profile->get_validation_errors());
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
				$maildata['from_name']    = \Config::get('site.member_setting_common.from_name');
				$maildata['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
				$maildata['subject']      = \Config::get('site.member_register_mail.subject');
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

		$this->set_title_and_breadcrumbs('メンバー登録確認', array('member/signup' => Config::get('term.signup')));
		$this->template->content = View::forge('member/register/index', array(
			'val' => $form_member_profile->get_validation(),
			'profiles' => $form_member_profile->get_profiles(),
			'public_flags' => $form_member_profile->get_member_profile_public_flags(),
			'member_pre' => $member_pre,
		));
	}

	private function send_register_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$data['body'] = <<< END
メンバー登録が完了しました。

====================
お名前: {$data['to_name']}
メールアドレス: {$data['to_address']}
パスワード: {$data['password']}
====================

END;

		Util_toolkit::sendmail($data);
	}
}
