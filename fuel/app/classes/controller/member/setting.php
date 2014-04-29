<?php
class WrongPasswordException extends \FuelException {}

class Controller_Member_setting extends Controller_Member
{
	protected $check_not_auth_action = array(
		'change_email',
	);

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
		$this->set_title_and_breadcrumbs(term(array('site.setting', 'site.item', 'site.list')), null, $this->u);
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
		$this->set_title_and_breadcrumbs(term(array('site.password', 'form.update')), array('member/setting/' => term(array('site.setting', 'form.update'))), $this->u);
		$this->template->content = View::forge('member/setting/password');
		$this->template->content->set_safe('html_form', $form->build('member/setting/change_password'));
	}

	public function action_change_password()
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();

		$form = $this->form_setting_password();
		$val  = $form->validation();

		if ($val->run())
		{
			$post = $val->validated();

			$data = array();
			$data['to_name']      = $this->u->name;
			$data['to_address']   = $this->u->member_auth->email;
			$data['from_name']    = \Config::get('mail.member_setting_common.from_name');
			$data['from_address'] = \Config::get('mail.member_setting_common.from_mail_address');
			$data['subject']      = \Config::get('mail.member_setting_password.subject');

			$term_password = term('site.password');
			$data['body'] = <<< END
{$data['to_name']} 様

{$term_password}を変更しました。

END;

			try
			{
				DB::start_transaction();
				$this->change_password($post['old_password'], $post['password']);
				DB::commit_transaction();
				Util_toolkit::sendmail($data);
				Session::set_flash('message', term('site.password').'を変更しました。');
				Response::redirect('member/setting');
			}
			catch(EmailValidationFailedException $e)
			{
				$this->display_error(term(array('site.password', 'form.update')).': 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
			}
			catch(EmailSendingFailedException $e)
			{
				$this->display_error(term(array('site.password', 'form.update')).': 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
			}
			catch(WrongPasswordException $e)
			{
				if (DB::in_transaction()) \DB::rollback_transaction();
				Session::set_flash('error', sprintf('現在の%sが正しくありません。', term('site.password')));
				$this->action_password();
				return;
			}
			catch(FuelException $e)
			{
				if (DB::in_transaction()) \DB::rollback_transaction();
				Session::set_flash('error', $e->getMessage());
				$this->action_password();
				return;
			}
		}
		else
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_password();
		}
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
		$this->set_title_and_breadcrumbs(term(array('site.email', 'form.update')), array('member/setting' => term(array('site.setting', 'form.update'))), $this->u);
		$this->template->content = View::forge('member/setting/email');
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

		if (Model_MemberAuth::get4email($post['email']))
		{
			Session::set_flash('error', sprintf('その%sは登録できません。', term('site.email')));
			$this->action_email();
			return;
		}

		try
		{
			$maildata = array();
			DB::start_transaction();
			$maildata['token'] = $this->save_member_email_pre($this->u->id, $post['email']);
			DB::commit_transaction();
			$maildata['to_name']      = $this->u->name;
			$maildata['to_address']   = $post['email'];
			$maildata['from_name']    = \Config::get('mail.member_setting_common.from_name');
			$maildata['from_address'] = \Config::get('mail.member_setting_common.from_mail_address');
			$maildata['subject']      = \Config::get('mail.member_confirm_change_email.subject');
			$this->send_confirm_change_email_mail($maildata);

			Session::set_flash('message', sprintf('新しい%s宛に確認用%sを送信しました。受信した%s内に記載された URL より%sの変更を完了してください。', term('site.email'), term('site.mail'), term('site.mail'), term('site.email')));
			Response::redirect('member/setting');
		}
		catch(EmailValidationFailedException $e)
		{
			$this->display_error(term(array('site.email', 'form.update')).': 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
		}
		catch(EmailSendingFailedException $e)
		{
			$this->display_error(term(array('site.email', 'form.update')).': 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
		}
		catch(FuelException $e)
		{
			if (DB::in_transaction())\DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
			$this->action_email();
			return;
		}
	}

	/**
	 * Execute change email.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_change_email()
	{
		$member_email_pre = Model_MemberEmailPre::get4token(Input::param('token'));
		if (!$member_email_pre || (Auth::check() && $member_email_pre->member_id != $this->u->id))
		{
			$this->display_error(null, null, 'error/403', 403);
			return;
		}

		$form = $this->form_change_email();
		$val  = $form->validation();

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$auth = Auth::instance();
			if ($val->run() && $auth->check_password())
			{
				try
				{
					DB::start_transaction();
					if (!$auth->update_user(array('email' => $member_email_pre->email)))
					{
						throw new FuelException('change email error.');
					}
					if (!$member = Model_Member::check_authority($member_email_pre->member_id))
					{
						throw new FuelException('change email error.');
					}
					$email = $member_email_pre->email;
					$member_email_pre->delete();// 仮登録情報の削除
					DB::commit_transaction();

					$maildata = array();
					$maildata['from_name']    = \Config::get('mail.member_setting_common.from_name');
					$maildata['from_address'] = \Config::get('mail.member_setting_common.from_mail_address');
					$maildata['subject']      = \Config::get('mail.member_change_email.subject');
					$maildata['to_address']   = $email;
					$maildata['to_name']      = $member->name;
					$this->send_change_email_mail($maildata);

					Session::set_flash('message', term('site.email').'を変更しました。');
					Response::redirect('member');
				}
				catch(EmailValidationFailedException $e)
				{
					$this->display_error(term('member.view').'登録: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
					return;
				}
				catch(EmailSendingFailedException $e)
				{
					$this->display_error(term('member.view').'登録: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
					return;
				}
				catch(\Auth\SimpleUserUpdateException $e)
				{
					if (DB::in_transaction())\DB::rollback_transaction();
					Session::set_flash('error', sprintf('その%sは登録できません。', term('site.email')));
				}
				catch(FuelException $e)
				{
					if (DB::in_transaction())\DB::rollback_transaction();
					Session::set_flash('error', term('site.email').'の変更に失敗しました。');
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
					Session::set_flash('error', term('site.password').'が正しくありません。');
				}
			}
		}

		if (Auth::check())
		{
			$this->set_title_and_breadcrumbs(
				term(array('site.email', 'form.update', 'form.confirm')),
				array('member/setting' => term(array('site.setting', 'form.update')),
				'member/setting/email' => term(array('site.email', 'form.update'))),
				$this->u
			);
		}
		else
		{
			$this->set_title_and_breadcrumbs(term(array('site.email', 'form.update', 'form.confirm')));
		}

		$this->template->content = View::forge('member/setting/change_email', array('val' => $val,'member_email_pre' => $member_email_pre));
	}

	public function form_setting_password()
	{
		$add_fields = array(
			'old_password' => Form_Util::get_model_field('member_auth', 'password', '', sprintf('現在の%s', term('site.password'))),
			'password' => Form_Util::get_model_field('member_auth', 'password', '', sprintf('新しい%s', term('site.password'))),
			'password_confirm' => Form_Util::get_model_field('member_auth', 'password', '', sprintf('新しい%s(確認用)', term('site.password'))),
		);
		$add_fields['password']['rules'][] = array('unmatch_field', 'old_password');
		$add_fields['password_confirm']['rules'][] = array('match_field', 'password');

		return Site_Util::get_form_instance('setting_password', null, true, $add_fields, array('value' => term('form.update')));
	}

	public function form_setting_email()
	{
		$add_fields = array(
			'email' => Form_Util::get_model_field('member_auth', 'email', '', sprintf('新しい%s', term('site.email'))),
			'email_confirm' => Form_Util::get_model_field('member_auth', 'email', '', sprintf('新しい%s(確認用)', term('site.email'))),
		);
		$form = \Site_Util::get_form_instance('setting_email', null, true, $add_fields, array('value' => term('form.update')));

		return $form;
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

	protected function change_password($old_password, $password)
	{
		$auth = Auth::instance();
		if (!$auth->change_password($old_password, $password))
		{
			throw new WrongPasswordException('change password error.');
		}
	}

	private function save_member_email_pre($member_id, $email)
	{
		$member_email_pre = Model_MemberEmailPre::get4member_id($member_id);
		if (!$member_email_pre) $member_email_pre = Model_MemberEmailPre::forge();

		$member_email_pre->member_id = $member_id;
		$member_email_pre->email     = $email;
		$member_email_pre->token     = Util_toolkit::create_hash();
		$member_email_pre->save();

		return $member_email_pre->token;
	}

	private function send_confirm_change_email_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$register_url = sprintf('%s?token=%s', uri::create('member/setting/change_email'), $data['token']);

		$term_email = term('site.email');
		$data['body'] = <<< END
こんにちは、{$data['to_name']}さん

まだ{$term_email}の変更は完了しておりません。

以下の URL をクリックすることにより、{$term_email}の変更が完了します。
{$register_url}

END;

		util_toolkit::sendmail($data);
	}

	private function send_change_email_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$term_email = term('site.email');
		$data['body'] = <<< END
こんにちは、{$data['to_name']}さん

{$term_email}の変更が完了しました。

====================
新しい{$term_email}:
{$data['to_address']}
====================

END;

		util_toolkit::sendmail($data);
	}
}
