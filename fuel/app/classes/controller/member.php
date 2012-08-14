<?php

class WrongPasswordException extends \FuelException {}

class Controller_Member extends Controller_Site
{
	//public $template = 'admin/template';

	protected $check_not_auth_action = array(
		'signup',
		'pre_register',
		'register',
		'home',
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
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
	 * Mmeber profile
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_home($id = null)
	{
		if ($id)
		{
			if (!$member = Model_Member::find()->where('id', $id)->get_one())
			{
				throw new \HttpNotFoundException;
			}
		}
		else
		{
			$member = $this->current_user;
		}

		$this->template->title = $member->name.' さんのページ';
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '/', $this->template->title => '');

		$list = \Note\Model_Note::find()->where('member_id', $id)->order_by('created_at', 'desc')->get();

		$this->template->subtitle = View::forge('_parts/home_subtitle', array('member' => $member));
		$this->template->content = \View::forge('member/home', array('member' => $member, 'list' => $list));
	}

	/**
	 * Mmeber signup
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_signup()
	{
		if (!$form = Fieldset::instance('pre_register'))
		{
			$form = $this->form();
		}

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->template->title = PRJ_SITE_NAME.'　メンバー登録';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '/', Config::get('site.term.signup') => '');
		$data = array('form' => $form);
		$this->template->content = View::forge('member/signup', $data);
		$this->template->content->set_safe('html_form', $form->build('/member/pre_register'));// form の action に入る
	}

	/**
	 * Execute pre register
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_pre_register()
	{
		Util_security::check_csrf();

		$form = $this->form();
		$val = $this->form()->validation();

		if ($val->run())
		{
			$post = $val->validated();

			try
			{
				$data = array();
				$data['name'] = $post['name'];
				$data['email']    = $post['email'];
				$data['password'] = $post['password'];
				$token = $this->save_pre_member($data);

				$maildata = array();
				$maildata['from_name']    = \Config::get('site.member_register_mail.from_name');
				$maildata['from_address'] = \Config::get('site.member_register_mail.from_mail_address');
				$maildata['subject']      = \Config::get('site.member_register_mail.subject');
				$maildata['to_address']   = $post['email'];
				$maildata['to_name']      = $post['name'];
				$maildata['password']     = $post['password'];
				$maildata['token']        = $token;
				$this->send_pre_register_mail($maildata);

				Session::set_flash('message', '仮登録が完了しました。受信したメール内に記載された URL より本登録を完了してください。');
				Response::redirect('site/login');
			}
			catch(EmailValidationFailedException $e)
			{
				$this->display_error('メンバー登録: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
			}
			catch(EmailSendingFailedException $e)
			{
				$this->display_error('メンバー登録: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
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
	 * Execute register
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_register()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		if (!$member_pre = $this->check_token())
		{
			$this->display_error('メンバー登録: 不正なURL');
			return;
		}

		$val = Validation::forge('register');
		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();

			$val->add('password', 'パスワード', array('type'=>'password'))
				->add_rule('trim')
				->add_rule('required')
				->add_rule('no_controll')
				->add_rule('min_length', 6)
				->add_rule('max_length', 20)
				->add_rule('match_value', $member_pre->password);
			$val->set_message('match_value', 'パスワードが正しくありません。');
			$val->add('token', '', array('type'=>'hidden'))
				->add_rule('required')
				->add_rule('no_controll');

			if ($val->run())
			{
				try
				{
					// create new member
					$auth = Auth::instance();
					if (!$member_id = $auth->create_user($member_pre->email, $member_pre->password, $member_pre->name))
					{
						throw new Exception('create member error.');
					}

					$maildata = array();
					$maildata['from_name']    = \Config::get('site.member_register_mail.from_name');
					$maildata['from_address'] = \Config::get('site.member_register_mail.from_mail_address');
					$maildata['subject']      = \Config::get('site.member_register_mail.subject');
					$maildata['to_address']   = $member_pre->email;
					$maildata['to_name']      = $member_pre->name;
					$maildata['password']     = $member_pre->password;
					$this->send_register_mail($maildata);

					// 仮登録情報の削除
					$email    = $member_pre->email;
					$password = $member_pre->password;
					$member_pre->delete();

					if ($auth->login($email, $password))
					{
						Session::set_flash('message', '登録が完了しました。');
						Response::redirect('member');
					}

					Session::set_flash('error', 'ログインに失敗しました');
					Response::redirect('site/login');
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
				catch(Auth\NormalUserUpdateException $e)
				{
					Session::set_flash('error', 'そのアドレスは登録できません');
				}
			}
			else
			{
				Session::set_flash('error', $val->show_errors());
			}
		}

		$this->template->title = 'メンバー登録確認';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '/', 'メンバー登録確認' => '');
		$data = array('val' => $val, 'member_pre' => $member_pre);
		$this->template->content = View::forge('member/register', $data);
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
		if (!$form = Fieldset::instance('leave'))
		{
			$form = $this->form_leave();
		}
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
			$data['to_address']   = $this->current_user->memberauth->email;
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
				$this->display_error('メンバー退会: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
			}
			catch(EmailSendingFailedException $e)
			{
				$this->display_error('メンバー退会: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
			}
			catch(Exception $e)
			{
				Session::set_flash('error', '退会に失敗しました。');
				$this->action_leave();
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
		if (!$form = Fieldset::instance('setting_password'))
		{
			$form = $this->form_setting_password();
		}

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
			$data['to_address']   = $this->current_user->memberauth->email;
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
				$this->display_error('パスワード変更: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
			}
			catch(EmailSendingFailedException $e)
			{
				$this->display_error('パスワード変更: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
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
		if (!$form = Fieldset::instance('setting_email'))
		{
			$form = $this->form_setting_email();
		}

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
				$this->display_error('メールアドレス変更: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
			}
			catch(EmailSendingFailedException $e)
			{
				$this->display_error('メールアドレス変更: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
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
		$form = Fieldset::forge('pre_register');

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

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Security::fetch_token()));

		return $form;
	}

	public function form_leave()
	{
		$form = Fieldset::forge('leave');

		$form->add('password', 'パスワード', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20);

		$form->add('submit', '', array('type'=>'submit', 'value' => '確認', 'class' => 'btn'));

		return $form;
	}

	public function form_setting_password()
	{
		$form = Fieldset::forge('setting_password');

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

		$form->add('submit', '', array('type'=>'submit', 'value' => '変更', 'class' => 'btn'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Security::fetch_token()));

		return $form;
	}

	public function form_setting_email()
	{
		$form = Fieldset::forge('setting_email', array('class' => 'form-horizontal'));

		$form->add('email', 'メールアドレス')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('valid_email');

		$form->add('email_confirm', 'メールアドレス(確認用)')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('match_field', 'email');

		$form->add('submit', '', array('type'=>'submit', 'value' => '変更', 'class' => 'btn'));
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

	private function check_token()
	{
		if ($member_pre = Model_MemberPre::find()->where('token', Input::param('token'))->get_one())
		{
			return $member_pre;
		}

		return false;
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

	private function save_pre_member($data)
	{
		$member_pre = new Model_MemberPre();
		$member_pre->name = $data['name'];
		$member_pre->email = $data['email'];
		$member_pre->password = $data['password'];
		$member_pre->token = Util_toolkit::create_hash();
		$member_pre->save();

		return $member_pre->token;
	}

	private function send_pre_register_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$register_url = sprintf('%s?token=%s', Uri::create('member/register'), $data['token']);
		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
こんにちは、{$to_name}さん

仮登録が完了しました。
まだ登録は完了しておりません。

以下のアドレスをクリックすることにより、{$site_name}アカウントの登録確認が完了します。
{$register_url}

上記の確認作業が完了しないと、{$site_name} のサービスが利用できません。

END;

		Util_toolkit::sendmail($data);
	}

	private function send_register_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;
		$register_url = sprintf('%s?token=%s', Uri::create('member/register'), $data['token']);

		$data['body'] = <<< END
メンバー登録が完了しました。

====================
お名前: {$data['name']}
メールアドレス: {$data['email']}
パスワード: {$data['password']}
====================

END;

		Util_toolkit::sendmail($data);
	}
}
