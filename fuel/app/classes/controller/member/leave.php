<?php

class Controller_Member_Leave extends Controller_Site
{
	protected $check_not_auth_action = array(
	);

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

		$auth = Auth::instance();
		if ($val->run() && $auth->check_password())
		{
			$this->set_title_and_breadcrumbs(
				term('site.left', 'form.confirm'),
				array('member/setting' => term('site.setting', 'form.update'), 'member/leave' => term('site.left')),
				$this->u
			);
			$this->template->content = View::forge('member/leave/confirm', array('input' => $val->validated()));
		}
		else
		{
			if ($val->show_errors())
			{
				Session::set_flash('error', $val->show_errors());
			}
			else
			{
				Session::set_flash('error', term('site.password').'が正しくありません');
			}
			$this->action_index();
		}
	}

	public function action_delete()
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();

		$form = $this->form_leave();
		$val  = $form->validation();

		$auth = Auth::instance();
		if ($val->run() && $auth->check_password())
		{
			$data = array();
			$data['to_name']      = $this->u->name;
			$data['to_address']   = $this->u->member_auth->email;
			$data['from_name']    = \Config::get('mail.member_leave_mail.from_name');
			$data['from_address'] = \Config::get('mail.member_leave_mail.from_mail_address');
			$data['subject']      = \Config::get('mail.member_leave_mail.subject');

			$data['body'] = <<< END
{$data['to_name']} 様

退会が完了しました。
END;

			try
			{
				DB::start_transaction();
				$auth->delete_user($this->u->id);
				DB::commit_transaction();
				$auth->logout();
				Util_toolkit::sendmail($data);
				Session::set_flash('message', term('site.left').'が完了しました。');
				Response::redirect(Config::get('site.login_uri.site'));
			}
			catch(EmailValidationFailedException $e)
			{
				$this->display_error('メンバー退会: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
			}
			catch(EmailSendingFailedException $e)
			{
				$this->display_error('メンバー退会: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
			}
			catch(FuelException $e)
			{
				if (DB::in_transaction()) DB::rollback_transaction();
				Session::set_flash('error', '退会に失敗しました。');
				$this->action_index();
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
				Session::set_flash('error', term('site.password').'が正しくありません');
			}
			$this->action_index();
		}
	}

	public function form_leave()
	{
		$add_fields = array('password' => Form_Util::get_model_field('member_auth', 'password'));
		$form = \Site_Util::get_form_instance('leave', null, true, $add_fields, array('value' => '確認'));

		return $form;
	}
}
