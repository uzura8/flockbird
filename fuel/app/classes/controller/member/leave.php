<?php

class Controller_Member_Leave extends Controller_Site
{
	protected $check_not_auth_action = array();

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
		$this->set_title_and_breadcrumbs(__('member_title_leave_service'), array(
			'member/setting' => term('site.setting'),
		), $this->u);
		$this->template->content = View::forge('member/leave/index');
		$this->template->content->set_safe('html_form', $form->build('member/leave/confirm'));// Set action on form attribute
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

		$breadcrumbs_middle_path = array('member/setting' => term('site.setting'));
		if (!$this->u->check_registered_oauth(true))
		{
			if (!$val->run())
			{
				Session::set_flash('error', $val->show_errors());
				$this->action_index();
				return;
			}
			if (!$this->auth_instance->check_password())
			{
				Session::set_flash('error', __('message_invalid_for', array('label' => t('site.password'))));
				$this->action_index();
				return;
			}

			$breadcrumbs_middle_path['member/leave'] = __('member_title_leave_service');
		}

		$this->set_title_and_breadcrumbs(
			__('member_title_confirmation_of_leave_service'),
			$breadcrumbs_middle_path,
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

		if (!$this->u->check_registered_oauth(true) && !$this->auth_instance->check_password())
		{
			Session::set_flash('error', __('message_invalid_for', array('label' => t('site.password'))));
			$this->action_index();
			return;
		}

		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			$message = Site_Member::remove($this->u);
			$this->auth_instance->logout();
			Session::set_flash('message', $message);
			Response::redirect(conf('login_uri.site'));
		}
		catch(EmailValidationFailedException $e)
		{
			Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
			$error_message = __('message_send_mail_error');
		}
		catch(EmailSendingFailedException $e)
		{
			Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
			$error_message = __('message_send_mail_error');
		}
		catch(SimpleUserUpdateException $e)
		{
			$is_transaction_rollback = true;
			$error_message = __('message_not_registered_for', array('label' => t('member.view')));
		}
		catch(Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = Site_Controller::get_error_message($e, true);
		}
		catch(FuelException $e)
		{
			$is_transaction_rollback = true;
			if (!$error_message = $e->getMessage()) $error_message = __('member_message_leave_failed');
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
		$add_fields = array();
		if (!$this->u->check_registered_oauth(true))
		{
			$add_fields = array('password' => Form_Util::get_model_field('member_auth', 'password'));
			$add_fields['password']['attributes']['class'] .= ' input-xlarge';
			$add_fields['password']['rules'][] = array('required');
		}
		$form = \Site_Util::get_form_instance('leave', null, true, $add_fields, array('value' => term('form.do_confirm')));

		return $form;
	}
}
