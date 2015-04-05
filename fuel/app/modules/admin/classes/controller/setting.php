<?php
namespace Admin;

class Controller_Setting extends Controller_Admin
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Admin setting index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->set_title_and_breadcrumbs(term('site.setting', 'form.update'));
		$this->template->content = \View::forge('setting/index');
	}

	/**
	 * Admin setting password
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_password()
	{
		if (!$form = \Fieldset::instance('setting_password'))
		{
			$form = $this->form_setting_password();
		}

		if (\Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs(term('site.password', 'form.update'), array('admin/setting' => term('site.setting', 'form.update')));
		$this->template->content = \View::forge('_parts/setting/password');
		$this->template->content->set_safe('html_form', $form->build('admin/setting/change_password'));
	}

	public function action_change_password()
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();

		$form = $this->form_setting_password();
		$val  = $form->validation();

		if ($val->run())
		{
			$post = $val->validated();

			$data = array();
			$data['to_name']      = $this->u->username;
			$data['to_address']   = $this->u->email;
			$data['from_name']    = conf('mail.admin.from_name');
			$data['from_address'] = conf('mail.admin.from_email');
			$data['subject']      = term('site.password', 'form.update', 'form.complete').'の'.term('site.notice');

			$term_password = term('site.password');
			$data['body'] = <<< END
{$data['to_name']} 様

{$term_password}を変更しました。

END;

			try
			{
				\DB::start_transaction();
				$this->change_password($post['old_password'], $post['password']);
				\DB::commit_transaction();
				\Util_toolkit::sendmail($data);
				\Session::set_flash('message', term('site.password').'を変更しました。');
				\Response::redirect('admin/setting');
			}
			catch(\EmailValidationFailedException $e)
			{
				$this->display_error(term('site.password', 'form.update').': 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
			}
			catch(\EmailSendingFailedException $e)
			{
				$this->display_error(term('site.password', 'form.update').': 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
			}
			catch(\WrongPasswordException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', sprintf('現在の%sが正しくありません。', term('site.password')));
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		else
		{
			\Session::set_flash('error', $val->show_errors());
		}

		$this->action_password();
	}

	/**
	 * Admin setting email
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_email()
	{
		if (!$form = \Fieldset::instance('setting_email'))
		{
			$form = $this->form_setting_email();
		}

		if (\Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs(term('site.email', 'form.update'), array('admin/setting' => term('site.setting', 'form.update')));
		$this->template->content = \View::forge('_parts/setting/email');
		$this->template->content->set_safe('html_form', $form->build('admin/setting/change_email'));
	}

	/**
	 * Admin change email.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_change_email()
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();

		$form = $this->form_setting_email();
		$val  = $form->validation();
		if ($val->run())
		{
			try
			{
				$post = $val->validated();
				$email = $post['email'];
				\DB::start_transaction();
				if (!$this->auth_instance->update_user(array('email' => $email)))
				{
					throw new \FuelException('change email error.');
				}
				\DB::commit_transaction();

				$maildata = array();
				$maildata['from_name']    = conf('mail.admin.from_name');
				$maildata['from_address'] = conf('mail.admin.from_email');
				$maildata['subject']      = term('site.email', 'form.update', 'form.complete').'の'.term('site.notice');
				$maildata['to_address']   = $email;
				$maildata['to_name']      = $this->u->username;
				$this->send_change_email_mail($maildata);

				\Session::set_flash('message', term('site.email').'を変更しました。');
				\Response::redirect('admin/setting');
			}
			catch(\EmailValidationFailedException $e)
			{
				$this->display_error(term('member.view').'登録: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
				return;
			}
			catch(\EmailSendingFailedException $e)
			{
				$this->display_error(term('member.view').'登録: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
				return;
			}
			catch(\Auth\SimpleUserUpdateException $e)
			{
				if (\DB::in_transaction())\DB::rollback_transaction();
				\Session::set_flash('error', sprintf('その%sは登録できません。', term('site.email')));
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction())\DB::rollback_transaction();
				\Session::set_flash('error', term('site.email').'の変更に失敗しました。');
			}
		}
		else
		{
			\Session::set_flash('error', $val->show_errors());
		}

		$this->action_email();
	}

	public function form_setting_password()
	{
		$add_fields = array(
			'old_password' => \Form_Util::get_model_field('admin_user', 'password', sprintf('現在の%s', term('site.password'))),
			'password' => \Form_Util::get_model_field('admin_user', 'password', sprintf('新しい%s', term('site.password'))),
			'password_confirm' => \Form_Util::get_model_field('admin_user', 'password', sprintf('新しい%s(確認用)', term('site.password'))),
		);
		$add_fields['password']['rules'][] = array('unmatch_field', 'old_password');
		$add_fields['password_confirm']['rules'][] = array('match_field', 'password');

		return \Site_Util::get_form_instance('setting_password', null, true, $add_fields, array('value' => term('form.do_update')));
	}

	private function form_setting_email()
	{
		$add_fields = array(
			'email' => \Form_Util::get_model_field('admin_user', 'email', sprintf('新しい%s', term('site.email'))),
			'email_confirm' => array(
				'label' => sprintf('新しい%s(確認用)', term('site.email')),
				'attributes' => array('type' => 'email', 'class' => 'input-xlarge form-control'),
				'rules' => array('required', array('match_field', 'email')),
			),
		);

		return \Site_Util::get_form_instance('setting_email', null, true, $add_fields, array('value' => term('form.do_update')));
	}

	private function change_password($old_password, $password)
	{
		if (!$this->auth_instance->change_password($old_password, $password))
		{
			throw new \WrongPasswordException('change password error.');
		}
	}

	private function send_change_email_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$term_email = term('site.email');
		$data['body'] = <<< END
こんにちは、{$data['to_name']}さん

{$term_email}の変更が完了しました。

END;

		\Util_Toolkit::sendmail($data);
	}
}
