<?php

class WrongPasswordException extends \FuelException {}

class Controller_Member extends Controller_Site
{
	//public $template = 'admin/template';

	protected $check_not_auth_action = array(
		'signup',
		'register',
	);

	public function before()
	{
		parent::before();

		if (!$this->check_not_auth_action() && !Auth::check()) Response::redirect('site/login');
		if ($this->check_not_auth_action() && Auth::check()) Response::redirect('member/index');
	}

	/**
	 * Mmeber index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = 'マイホーム';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '/', Config::get('site.term.myhome') => '');

		$this->template->content = View::forge('member/index');
	}

	/**
	 * Mmeber signup
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_signup()
	{
		$form = $this->form();

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->template->title = PRJ_SITE_NAME.'　メンバー登録';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '/', Config::get('site.term.signup') => '');
		$data = array('form' => $form);
		$this->template->content = View::forge('member/signup', $data);
		$this->template->content->set_safe('html_form', $form->build('/member/register'));// form の action に入る
	}

	/**
	 * Contact form register
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_register()
	{
		Util_security::check_csrf();

		$form = $this->form();
		$val = $this->form()->validation();

		if ($val->run())
		{
			$post = $val->validated();

			$data = array();
			$data['name'] = $post['name'];
			$data['email']    = $post['email'];
			$data['password'] = $post['password'];

			$maildata['from_name']    = \Config::get('site.member_register_mail.from_name');
			$maildata['from_address'] = \Config::get('site.member_register_mail.from_mail_address');
			$maildata['subject']      = \Config::get('site.member_register_mail.subject');
			$maildata['to_address']    = $post['email'];
			$maildata['to_name'] = $post['name'];

			$maildata['body'] = <<< END
メンバー登録が完了しました。

====================
お名前: {$post['name']}
メールアドレス: {$post['email']}
パスワード: {$post['password']}
====================
END;

			try
			{
				$this->save($data);
				Util_toolkit::sendmail($maildata);
				Session::set_flash('message', 'メンバー登録が完了しました。ログインしてください。');
				Response::redirect('site/login');
			}
			catch(EmailValidationFailedException $e)
			{
				$this->template->title = 'メンバー登録: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email validation error: ' .
					$e->getMessage()
				);
			}
			catch(EmailSendingFailedException $e)
			{
				$this->template->title = 'メンバー登録: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email sending error: ' .
					$e->getMessage()
				);
			}
			catch(Auth\NormalUserUpdateException $e)
			{
				Session::set_flash('error', 'そのアドレスは登録できません');
				$this->action_signup();
			}
		}
		else
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_signup();
		}
	}

	/**
	 * Mmeber setting
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_setting()
	{
		$title = '設定変更';
		$this->template->title = $title;
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			$title => '',
		);

		$this->template->content = View::forge('member/setting');
	}

	/**
	 * Mmeber leave
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_leave()
	{
		$form = $this->form_leave();

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}

		$title = Config::get('site.term.member_leave');
		$this->template->title = $title;
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			$title => '',
		);
		$this->template->content = View::forge('member/leave');
		$this->template->content->set_safe('html_form', $form->build('/member/leave_confirm'));// form の action に入る
	}

	/**
	 * Mmeber leave_confirm
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_leave_confirm()
	{
		$form = $this->form_leave();
		$val  = $form->validation();

		if ($val->run() && $this->check_password())
		{
			$data = array('input' => $val->validated());
			$title = Config::get('site.term.member_leave').'確認';
			$this->template->content = View::forge('member/leave_confirm', $data);
			$this->template->title = $title;
			$this->template->header_title = site_title();
			$this->template->breadcrumbs = array(
				Config::get('site.term.toppage') => '/',
				Config::get('site.term.myhome') => '/member/',
				$title => '',
			);
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
			$this->action_leave();
		}
	}

	public function action_delete()
	{
		Util_security::check_csrf();

		$form = $this->form_leave();
		$val  = $form->validation();

		if ($val->run() && $this->check_password())
		{
			$data = array();
			$data['to_name']      = $this->current_user->name;
			$data['to_address']   = $this->current_user->member_auth->email;
			$data['from_name']    = \Config::get('site.member_leave_mail.from_name');
			$data['from_address'] = \Config::get('site.member_leave_mail.from_mail_address');
			$data['subject']      = \Config::get('site.member_leave_mail.subject');

			$data['body'] = <<< END
{$data['to_name']} 様

退会が完了しました。
END;

			try
			{
				$this->delete_user($this->current_user->id);
				Util_toolkit::sendmail($data);
				Session::set_flash('message', '退会が完了しました。');
				Response::redirect('site/login');
			}
			catch(EmailValidationFailedException $e)
			{
				$this->template->title = 'メンバー退会: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email validation error: ' .
					$e->getMessage()
				);
			}
			catch(EmailSendingFailedException $e)
			{
				$this->template->title = 'メンバー退会: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email sending error: ' .
					$e->getMessage()
				);
			}
			catch(Exception $e)
			{
				Session::set_flash('error', '退会に失敗しました。');
				$this->action_setting_password();
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
			$this->action_leave();
		}
	}

	/**
	 * Mmeber setting password
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_setting_password()
	{
		$form = $this->form_setting_password();

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}

		$title = 'パスワード変更';
		$this->template->title = $title;
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			'設定変更' => '/member/setting/',
			$title => '',
		);
		$this->template->content = View::forge('member/setting_password');
		$this->template->content->set_safe('html_form', $form->build('/member/change_password'));// form の action に入る
	}

	public function action_change_password()
	{
		Util_security::check_csrf();

		$form = $this->form_setting_password();
		$val  = $form->validation();

		if ($val->run())
		{
			$post = $val->validated();

			$data = array();
			$data['to_name']      = $this->current_user->name;
			$data['to_address']   = $this->current_user->member_auth->email;
			$data['from_name']    = \Config::get('site.member_setting_common.from_name');
			$data['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
			$data['subject']      = \Config::get('site.member_setting_password.subject');

			$data['body'] = <<< END
{$data['to_name']} 様

パスワードを変更しました。

================================
新しいパスワード: {$post['password']}
================================
END;

			try
			{
				$this->change_password($post['old_password'], $post['password']);
				Util_toolkit::sendmail($data);
				Session::set_flash('message', 'パスワードを変更しました。再度ログインしてください。');
				Response::redirect('site/login');
			}
			catch(EmailValidationFailedException $e)
			{
				$this->template->title = 'パスワード変更: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email validation error: ' .
					$e->getMessage()
				);
			}
			catch(EmailSendingFailedException $e)
			{
				$this->template->title = 'パスワード変更: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email sending error: ' .
					$e->getMessage()
				);
			}
			catch(WrongPasswordException $e)
			{
				Session::set_flash('error', '現在のパスワードが正しくありません。');
				$this->action_setting_password();
			}
		}
		else
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_setting_password();
		}
	}

	/**
	 * Mmeber setting email
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_setting_email()
	{
		$form = $this->form_setting_email();

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$title = 'メールアドレス変更';
		$this->template->title = $title;
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			'設定変更' => '/member/setting/',
			$title => '',
		);
		$this->template->content = View::forge('member/setting_email');
		$this->template->content->set_safe('html_form', $form->build('/member/change_email'));// form の action に入る
	}

	/**
	 * Mmeber change email
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_change_email()
	{
		Util_security::check_csrf();

		$form = $this->form_setting_email();
		$val  = $form->validation();

		if ($val->run())
		{
			$post = $val->validated();

			$data = array();
			$data['to_name']      = $this->current_user->name;
			$data['to_address']   = $post['email'];
			$data['from_name']    = \Config::get('site.member_setting_common.from_name');
			$data['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
			$data['subject']      = \Config::get('site.member_setting_email.subject');

			$data['body'] = <<< END
{$data['to_name']} 様

メールアドレスを変更しました。

====================
メールアドレス: {$post['email']}
====================
END;

			try
			{
				$this->change_email($post['email']);
				Util_toolkit::sendmail($data);
				Session::set_flash('message', 'メールアドレスを変更しました。再度ログインしてください。');
				Response::redirect('site/login');
			}
			catch(EmailValidationFailedException $e)
			{
				$this->template->title = 'メールアドレス変更: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email validation error: ' .
					$e->getMessage()
				);
			}
			catch(EmailSendingFailedException $e)
			{
				$this->template->title = 'メールアドレス変更: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email sending error: ' .
					$e->getMessage()
				);
			}
			catch(Auth\NormalUserUpdateException $e)
			{
				Session::set_flash('error', 'そのアドレスは登録できません');
				$this->action_setting_email();
			}
		}
		else
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_setting_email();
		}
	}

	public function form()
	{
		$form = Fieldset::forge();

		$form->add('name', '名前')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('max_length', 50);

		$form->add('email', 'メールアドレス')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('valid_email');

		$form->add('password', 'パスワード', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20);

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Security::fetch_token()));

		return $form;
	}

	public function form_leave()
	{
		$form = Fieldset::forge();

		$form->add('password', 'パスワード', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20);

		$form->add('submit', '', array('type'=>'submit', 'value' => '確認'));

		return $form;
	}

	public function form_setting_password()
	{
		$form = Fieldset::forge();

		$form->add('old_password', '現在のパスワード', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20);

		$form->add('password', '新しいパスワード', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20)
			->add_rule('unmatch_field', 'old_password');

		$form->add('password_confirm', '新しいパスワード(確認用)', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('match_field', 'password');

		$form->add('submit', '', array('type'=>'submit', 'value' => '変更'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Security::fetch_token()));

		return $form;
	}

	public function form_setting_email()
	{
		$form = Fieldset::forge();

		$form->add('email', 'メールアドレス')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('valid_email');

		$form->add('email_confirm', 'メールアドレス(確認用)')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('match_field', 'email');

		$form->add('submit', '', array('type'=>'submit', 'value' => '変更'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Security::fetch_token()));

		return $form;
	}

	protected function change_email($email)
	{
		$auth = Auth::instance();
		if (!$auth->update_user(array('email' => $email)))
		{
			throw new Exception('change email error.');
		}

		return $auth->logout();
	}

	protected function check_password($password = '')
	{
		$auth = Auth::instance();
		return $auth->check_password($password);
	}

	protected function change_password($old_password, $password)
	{
		$auth = Auth::instance();
		if (!$auth->change_password($old_password, $password))
		{
			throw new WrongPasswordException('change password error.');
		}

		return $auth->logout();
	}

	protected function delete_user($member_id)
	{
		$auth = Auth::instance();
		return $auth->delete_user($member_id) && $auth->logout();
	}

	public function save($data)
	{
		// create new member
		$auth = Auth::instance();
		if (!$member_id = $auth->create_user($data['email'], $data['password'], $data['name']))
		{
			throw new Exception('create member error.');
		}

		return $member_id;
	}
}
