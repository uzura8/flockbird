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

		$message = term('site.password').'のリセット方法をメールで送信しました。';
		if (!$member_auth = Model_MemberAuth::get4email($post['email']))
		{
			Session::set_flash('message', $message);
			Response::redirect(conf('login_uri.site'));
			return;
		}

		$member = Model_Member::check_authority($member_auth->member_id);
		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			$maildata = array();
			DB::start_transaction();
			$token = Model_MemberPasswordPre::save_with_token($member_auth->member_id, $post['email']);
			DB::commit_transaction();

			$mail = new Site_Mail('memberResendPassword');
			$mail->send($post['email'], array(
				'to_name' => $member->name,
				'register_url' => sprintf('%s?token=%s', uri::create('member/recover/reset_password'), $token),
			));

			Session::set_flash('message', $message);
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
		catch(\Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = \Site_Controller::get_error_message($e, true);
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

		$this->action_resend_password();
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
		if (!$member_password_pre || Site_Util::check_token_lifetime($member_password_pre->created_at, conf('member.recover.password.token_lifetime')))
		{
			Session::set_flash('error', sprintf('URLが%sです。', term('form.disabled')));
			throw new HttpNotFoundException;
		}

		$form = $this->form_reset_password();
		$val  = $form->validation();

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$auth = Auth::instance();

			$error_message = '';
			$is_transaction_rollback = false;
			try
			{
				if (!$val->run())
				{
					throw new FuelException($val->show_errors() ?: term('site.password').'が正しくありません');
				}
				$post = $val->validated();
				$to_email = $member_password_pre->email;
				$to_name  = $member_password_pre->member->name;

				DB::start_transaction();
				$auth->change_password_simple($member_password_pre->member_id, $post['password']);
				$member_password_pre->delete();// 仮登録情報の削除
				DB::commit_transaction();

				$mail = new Site_Mail('memberResetPassword');
				$mail->send($to_email, array('to_name' => $to_name));

				$auth->login($to_email, $post['password']);
				Session::set_flash('message', term('site.password').'を登録しました。');
				Response::redirect('member');
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
			catch(Auth\SimpleUserUpdateException $e)
			{
				$is_transaction_rollback = true;
				$error_message = term('site.password').'の登録に失敗しました。';
			}
			catch(\Database_Exception $e)
			{
				$is_transaction_rollback = true;
				$error_message = \Site_Controller::get_error_message($e, true);
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

		$this->set_title_and_breadcrumbs(term('site.password').'の再登録');
		$data = array('val' => $val, 'member_password_pre' => $member_password_pre);
		$this->template->content = View::forge('member/recover/reset_password', $data);
		$this->template->content->set_safe('html_form', $form->build('member/recover/reset_password'));// form の action に入る
	}

	public function form_resend_password()
	{
		$add_fields = array('email' => Form_Util::get_model_field('member_auth', 'email', null, 'unique'));

		return Site_Util::get_form_instance('resend_password', null, true, $add_fields, array('value' => term('form.submit')));
	}

	public function form_reset_password()
	{
		$add_fields = array(
			'password' => Form_Util::get_model_field('member_auth', 'password', sprintf('新しい%s', term('site.password'))),
			'password_confirm' => Form_Util::get_model_field('member_auth', 'password', sprintf('新しい%s(確認用)', term('site.password'))),
			'token' => Form_Util::get_model_field('member_pre', 'token'),
		);
		$add_fields['token']['attributes'] = array('type'=>'hidden', 'value' => Input::param('token'));
		$add_fields['password_confirm']['rules'][] = array('match_field', 'password');

		return Site_Util::get_form_instance('reset_password', null, true, $add_fields, array('value' => '変更'));
	}
}
