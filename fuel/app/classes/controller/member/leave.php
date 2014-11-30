<?php

class Controller_Member_Leave extends Controller_Site
{
	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber leave
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		if (!$form = Fieldset::instance('leave'))
		{
			$form = $this->form_leave();
		}
		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs(term('site.left'), array('member/setting' => term('site.setting', 'form.update')), $this->u);
		$this->template->content = View::forge('member/leave/index');
		$this->template->content->set_safe('html_form', $form->build('member/leave/confirm'));// form の action に入る
	}

	/**
	 * Mmeber confirm
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm()
	{
		$form = $this->form_leave();
		$val  = $form->validation();
		if (!$val->run())
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_index();
			return;
		}

		$auth = Auth::instance();
		if (!$auth->check_password())
		{
			Session::set_flash('error', term('site.password').'が正しくありません');
			$this->action_index();
			return;
		}

		$this->set_title_and_breadcrumbs(
			term('site.left', 'form.confirm'),
			array('member/setting' => term('site.setting', 'form.update'), 'member/leave' => term('site.left')),
			$this->u
		);
		$this->template->content = View::forge('member/leave/confirm', array('input' => $val->validated()));
	}

	public function action_delete()
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();

		$form = $this->form_leave();
		$val  = $form->validation();
		if (!$val->run())
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_index();
			return;
		}

		//$auth = Auth::instance();
		if (!$this->auth_instance->check_password())
		{
			Session::set_flash('error', term('site.password').'が正しくありません');
			$this->action_index();
			return;
		}

		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			$to_name = $this->u->name;
			$to_email = $this->u->member_auth->email;
			DB::start_transaction();
			$this->auth_instance->logout();
			$this->auth_instance->delete_user($this->u->id);
			unset($this->auth_instance);
			DB::commit_transaction();

			$mail = new Site_Mail('memberLeave');
			$mail->send($to_email, array('to_name' => $to_name));

			Session::set_flash('message', term('site.left').'が完了しました。');
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
		catch(SimpleUserUpdateException $e)
		{
			$is_transaction_rollback = true;
			$error_message = term('member.view').'が存在しません。';
		}
		catch(Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = Util_Db::get_db_error_message($e);
		}
		catch(FuelException $e)
		{
			$is_transaction_rollback = true;
			if (!$error_message = $e->getMessage()) $error_message = term('site.left').'に失敗しました。';
		}
		if ($error_message)
		{
			if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
			Session::set_flash('error', $error_message);
		}

		$this->action_index();
	}

	public function form_leave()
	{
		$add_fields = array('password' => Form_Util::get_model_field('member_auth', 'password'));
		$form = \Site_Util::get_form_instance('leave', null, true, $add_fields, array('value' => term('form.do_confirm')));

		return $form;
	}
}
