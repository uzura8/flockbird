<?php

class Controller_Member_setting extends Controller_Member
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber setting
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->set_title_and_breadcrumbs(term('site.setting', 'site.item', 'site.list'), null, $this->u);
		$this->template->content = View::forge('member/setting/index');
	}

	/**
	 * Mmeber setting password
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_password()
	{
		if (!$form = Fieldset::instance('setting_password'))
		{
			$form = $this->form_setting_password();
		}

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs(term('site.password', 'form.update'), array('member/setting/' => term('site.setting', 'form.update')), $this->u);
		$this->template->content = View::forge('_parts/setting/password');
		$this->template->content->set_safe('html_form', $form->build('member/setting/change_password'));
	}

	public function action_change_password()
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();

		$form = $this->form_setting_password();
		$val  = $form->validation();

		if (!$val->run())
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_password();
			return;
		}
		$post = $val->validated();

		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			DB::start_transaction();
			$this->change_password($post['old_password'], $post['password']);
			DB::commit_transaction();

			$mail = new Site_Mail('memberSettingPassword');
			$mail->send($this->u->member_auth->email, array('to_name' => $this->u->name));

			Session::set_flash('message', term('site.password').'を変更しました。');
			Response::redirect('member/setting');
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
		catch(WrongPasswordException $e)
		{
			$is_transaction_rollback = true;
			$error_message = sprintf('現在の%sが正しくありません。', term('site.password'));
		}
		catch(Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = Site_Controller::get_error_message($e, true);
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

		$this->action_password();
	}

	/**
	 * Mmeber setting email
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_email()
	{
		if (!$form = Fieldset::instance('setting_email'))
		{
			$form = $this->form_setting_email();
		}

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs(term('site.email', 'form.update'), array('member/setting' => term('site.setting', 'form.update')), $this->u);
		$this->template->content = View::forge('_parts/setting/email');
		$this->template->content->set_safe('html_form', $form->build('member/setting/confirm_change_email'));// form の action に入る
	}

	/**
	 * Confirm change email
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm_change_email()
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();

		$form = $this->form_setting_email();
		$val  = $form->validation();

		if (!$val->run())
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_email();
			return;
		}
		$post = $val->validated();

		$email = term('site.email');
		$message = sprintf("新しい{$email}宛に確認用%sを送信しました。受信した{$email}内に記載された URL より{$email}の変更を完了してください。", term('site.mail'));
		$redirect_uri = 'member/setting';
		if (Model_MemberAuth::get4email($post['email']))
		{
			if (conf('member.setting.email.hideUniqueCheck'))
			{
				Session::set_flash('message', $message);
				Response::redirect($redirect_uri);
			}

			Session::set_flash('error', sprintf('その%sは登録できません。', term('site.email')));
			$this->action_email();
			return;
		}

		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			DB::start_transaction();
			$token = Model_MemberEmailPre::save_with_token($this->u->id, $post['email']);
			DB::commit_transaction();

			$mail = new Site_Mail('memberChangeEmailConfirm');
			$mail->send($this->u->member_auth->email, array(
				'to_name' => $this->u->name,
				'register_url' => sprintf('%s?token=%s', Uri::Create('member/setting/change_email'), $token),
			));

			Session::set_flash('message', $message);
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
		catch(Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = Site_Controller::get_error_message($e, true);
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

		$this->action_email();
	}

	/**
	 * Execute change email.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_change_email()
	{
		if (!$member_email_pre = $this->check_token_change_email())
		{
			throw new HttpNotFoundException('URLが無効です。');
		}

		$form = $this->form_change_email();
		$val  = $form->validation();

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$auth = Auth::instance();

			$error_message = '';
			$is_transaction_rollback = false;
			try
			{
				if (!$val->run()) throw new FuelException($val->show_errors());
				if (!$auth->check_password()) throw new FuelException(term('site.password').'が正しくありません。');
				$post = $val->validated();

				DB::start_transaction();
				if (!$auth->update_user(array('email' => $member_email_pre->email, 'old_password' => $post['password'], 'password' => $post['password'])))
				{
					throw new FuelException('change email error.');
				}
				$member = Model_Member::check_authority($member_email_pre->member_id);
				$email = $member_email_pre->email;
				$member_email_pre->delete();// 仮登録情報の削除
				DB::commit_transaction();

				$mail = new Site_Mail('memberChangeEmail');
				$mail->send($email, array('to_name' => $member->name));

				Session::set_flash('message', term('site.email').'を変更しました。');
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
				$error_message = term('site.email').'の変更に失敗しました。';
			}
			catch(\Database_Exception $e)
			{
				$is_transaction_rollback = true;
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(FuelException $e)
			{
				$is_transaction_rollback = true;
				if (!$error_message = $e->getMessage()) $error_message = term('site.email').'の変更に失敗しました。';
			}
			if ($error_message)
			{
				if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
				Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(
			term('site.email', 'form.update', 'form.confirm'),
			array('member/setting' => term('site.setting', 'form.update'),
			'member/setting/email' => term('site.email', 'form.update')),
			$this->u
		);

		$this->template->content = View::forge('member/setting/change_email', array('val' => $val,'member_email_pre' => $member_email_pre));
	}

	public function form_setting_password()
	{
		$add_fields = array(
			'old_password' => Form_Util::get_model_field('member_auth', 'password', sprintf('現在の%s', term('site.password'))),
			'password' => Form_Util::get_model_field('member_auth', 'password', sprintf('新しい%s', term('site.password'))),
			'password_confirm' => Form_Util::get_model_field('member_auth', 'password', sprintf('新しい%s(確認用)', term('site.password'))),
		);
		$add_fields['password']['rules'][] = array('unmatch_field', 'old_password');
		$add_fields['password_confirm']['rules'][] = array('match_field', 'password');

		return Site_Util::get_form_instance('setting_password', null, true, $add_fields, array('value' => term('form.update')));
	}

	public function form_setting_email()
	{
		$add_fields = array(
			'email' => Form_Util::get_model_field('member_auth', 'email', sprintf('新しい%s', term('site.email'))),
			'email_confirm' => array(
				'label' => sprintf('新しい%s(確認用)', term('site.email')),
				'attributes' => array('type' => 'email', 'class' => 'input-xlarge form-control'),
				'rules' => array('required', array('match_field', 'email')),
			),
		);

		return Site_Util::get_form_instance('setting_email', null, true, $add_fields, array('value' => term('form.update')));
	}

	public function form_change_email()
	{
		$add_fields = array(
			'password' => Form_Util::get_model_field('member_auth', 'password'),
			'token' => Form_Util::get_model_field('member_pre', 'token'),
		);
		$add_fields['token']['attributes'] = array('type'=>'hidden', 'value' => Input::param('token'));

		return Site_Util::get_form_instance('change_email', null, true, $add_fields, array('value' => term('form.update')));
	}

	private function change_password($old_password, $password)
	{
		$auth = Auth::instance();
		if (!$auth->change_password($old_password, $password))
		{
			throw new WrongPasswordException('change password error.');
		}
	}

	private function check_token_change_email()
	{
		if (!$member_email_pre = Model_MemberEmailPre::get4token(Input::param('token'))) return false;
		if (Site_Util::check_token_lifetime($member_email_pre->created_at, term('member.setting.email.token_lifetime'))) return false;
		if ($member_email_pre->member_id != $this->u->id) return false;

		return $member_email_pre;
	}
}
