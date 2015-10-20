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
		catch(\Auth\SimpleUserUpdateException $e)
		{
			$is_transaction_rollback = true;
			$error_message = term('site.password').'の変更に失敗しました。';
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

	public function form_setting_password()
	{
		$add_fields = array(
			'old_password' => Form_Util::get_model_field('member_auth', 'password', sprintf('現在の%s', term('site.password'))),
			'password' => Form_Util::get_model_field('member_auth', 'password', sprintf('新しい%s', term('site.password'))),
			'password_confirm' => Form_Util::get_model_field('member_auth', 'password', sprintf('新しい%s(確認用)', term('site.password'))),
		);
		$add_fields['old_password']['attributes']['class'] .= ' input-xlarge';
		$add_fields['password']['attributes']['class'] .= ' input-xlarge';
		$add_fields['password']['rules'][] = array('unmatch_field', 'old_password');
		$add_fields['password_confirm']['attributes']['class'] .= ' input-xlarge';
		$add_fields['password_confirm']['rules'][] = array('match_field', 'password');

		return Site_Util::get_form_instance('setting_password', null, true, $add_fields, array('value' => term('form.update')));
	}

	private function change_password($old_password, $password)
	{
		$auth = Auth::instance();
		if (!$auth->change_password($old_password, $password))
		{
			throw new WrongPasswordException('change password error.');
		}
	}
}
