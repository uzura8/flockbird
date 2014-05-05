<?php

class Controller_Member_Recover extends Controller_Site
{
	protected $check_not_auth_action = array(
		'resend_password',
		'send_reset_password_mail',
		'reset_password',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Resend password
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_resend_password()
	{
		if (!$form = Fieldset::instance('resend_password'))
		{
			$form = $this->form_resend_password();
		}

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs(term('site.password').'の再設定');
		$this->template->content = View::forge('member/recover/resend_password');
		$this->template->content->set_safe('html_form', $form->build('member/recover/send_reset_password_mail'));// form の action に入る
	}

	/**
	 * Confirm reset password
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_send_reset_password_mail()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		Util_security::check_method('POST');
		Util_security::check_csrf();

		$form = $this->form_resend_password();
		$val  = $form->validation();

		if (!$val->run())
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_resend_password();
			return;
		}
		$post = $val->validated();

		$message = 'パスワードのリセット方法をメールで送信しました。';
		if (!$member_auth = Model_MemberAuth::query()->where('email', $post['email'])->related('member')->get_one())
		{
			Session::set_flash('message', $message);
			Response::redirect(Config::get('site.login_uri.site'));
			return;
		}

		try
		{
			$maildata = array();
			DB::start_transaction();
			$maildata['token'] = $this->save_member_password_pre($member_auth->member_id, $post['email']);
			DB::commit_transaction();
			$maildata['to_name']      = $member_auth->member->name;
			$maildata['to_address']   = $post['email'];
			$maildata['from_name']    = \Config::get('mail.member_setting_common.from_name');
			$maildata['from_address'] = \Config::get('mail.member_setting_common.from_mail_address');
			$maildata['subject']      = \Config::get('mail.member_resend_password.subject');
			$this->send_confirm_reset_password_mail($maildata);

			Session::set_flash('message', $message);
			Response::redirect(Config::get('site.login_uri.site'));
		}
		catch(EmailValidationFailedException $e)
		{
			$this->display_error('パスワードのリセット: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
		}
		catch(EmailSendingFailedException $e)
		{
			$this->display_error('パスワードのリセット: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
		}
		catch(FuelException $e)
		{
			if (DB::in_transaction())\DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
			$this->action_resend_password();
			return;
		}
	}

	/**
	 * Execute reset password.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_reset_password()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		$member_password_pre = Model_MemberPasswordPre::get4token(Input::param('token'));
		if (!$member_password_pre || Site_Util::check_token_lifetime($member_password_pre->created_at, term('member.recover.password.token_lifetime')))
		{
			$this->display_error('メンバー登録: 不正なURL');
			return;
		}

		$form = $this->form_reset_password();
		$val  = $form->validation();

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$auth = Auth::instance();

			if ($val->run())
			{
				$post = $val->validated();
				try
				{
					$maildata = array();
					$maildata['from_name']    = Config::get('mail.member_setting_common.from_name');
					$maildata['from_address'] = Config::get('mail.member_setting_common.from_mail_address');
					$maildata['subject']      = Config::get('mail.member_reset_password.subject');
					$maildata['to_address']   = $member_password_pre->email;
					$maildata['to_name']      = $member_password_pre->member->name;
					DB::start_transaction();
					$auth->change_password_simple($member_password_pre->member_id, $post['password']);
					$member_password_pre->delete();// 仮登録情報の削除
					DB::commit_transaction();
					$this->send_reset_password_mail($maildata);

					$auth->login($member_password_pre->email, $post['password']);
					Session::set_flash('message', 'パスワードを登録しました。');
					Response::redirect('member');
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
				catch(Auth\SimpleUserUpdateException $e)
				{
					if (DB::in_transaction())\DB::rollback_transaction();
					Session::set_flash('error', 'パスワードの登録に失敗しました。');
				}
			}
			else
			{
				if ($val->show_errors())
				{
					Session::set_flash('error', $val->show_errors());
				}
				else
				{
					Session::set_flash('error', 'パスワードが正しくありません');
				}
			}
		}

		$this->set_title_and_breadcrumbs('パスワードの再登録');
		$data = array('val' => $val, 'member_password_pre' => $member_password_pre);
		$this->template->content = View::forge('member/recover/reset_password', $data);
		$this->template->content->set_safe('html_form', $form->build('member/recover/reset_password'));// form の action に入る
	}

	public function form_resend_password()
	{
		$add_fields = array('email' => Form_Util::get_model_field('member_auth', 'email'));

		return Site_Util::get_form_instance('resend_password', null, true, $add_fields, array('value' => term('form.submit')));
	}

	public function form_reset_password()
	{
		$add_fields = array(
			'password' => Form_Util::get_model_field('member_auth', 'password', '', sprintf('新しい%s', term('site.password'))),
			'password_confirm' => Form_Util::get_model_field('member_auth', 'password', '', sprintf('新しい%s(確認用)', term('site.password'))),
			'token' => Form_Util::get_model_field('member_pre', 'token'),
		);
		$add_fields['token']['attributes'] = array('type'=>'hidden', 'value' => Input::param('token'));
		$add_fields['password_confirm']['rules'][] = array('match_field', 'password');

		return Site_Util::get_form_instance('reset_password', null, true, $add_fields, array('value' => '変更'));
	}

	private function save_member_password_pre($member_id, $email)
	{
		$member_password_pre = Model_MemberPasswordPre::get4member_id($member_id);
		if (!$member_password_pre) $member_password_pre = Model_MemberPasswordPre::forge();

		$member_password_pre->member_id = $member_id;
		$member_password_pre->email = $email;
		$member_password_pre->token = Util_toolkit::create_hash();
		$member_password_pre->save();

		return $member_password_pre->token;
	}

	private function send_confirm_reset_password_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$register_url = sprintf('%s?token=%s', uri::create('member/recover/reset_password'), $data['token']);
		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
こんにちは、{$data['to_name']}さん

{$site_name} は、あなたのアカウントのパスワードをリセットするように依頼を受けました。

パスワードをリセットしたい場合、下記のリンクをクリックしてください (もしくは、URLをコピペしてブラウザに入力してください)。
{$register_url}

パスワードをリセットしたくない場合は、このメッセージを無視してください。 パスワードはリセットされません。

END;

		util_toolkit::sendmail($data);
	}

	private function send_reset_password_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
{$data['to_name']} さん

パスワードを再登録しました。

END;

		util_toolkit::sendmail($data);
	}

}
