<?php

class Controller_Member extends Controller_Site
{
	//public $template = 'admin/template';

	protected $check_not_auth_action = array(
		'signup',
		'confirm_register',
		'register',
		'home',
		'resend_password',
		'confirm_reset_password',
		'reset_password',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->set_title_and_breadcrumbs(Config::get('term.myhome'));
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
			$member = $this->u;
		}

		$this->set_title_and_breadcrumbs($member->name.' さんのページ');
		$this->template->subtitle = View::forge('_parts/home_subtitle', array('member' => $member));

		$list = \Note\Model_Note::find()->where('member_id', $id)->order_by('created_at', 'desc')->get();
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
		if (!$form = Fieldset::instance('confirm_register'))
		{
			$form = $this->form();
		}

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs(Config::get('term.signup'));
		$this->template->content = View::forge('member/signup', array('form' => $form));
		$this->template->content->set_safe('html_form', $form->build('/member/confirm_register'));// form の action に入る
	}

	/**
	 * Execute confirm register
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm_register()
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();

		if (!$form = Fieldset::instance('confirm_register'))
		{
			$form = $this->form();
		}
		$val = $form->validation();

		if ($val->run())
		{
			$post = $val->validated();

			try
			{
				if (Model_MemberAuth::find()->where('email', $post['email'])->get_one())
				{
					throw new Exception('そのメールアドレスは登録できません。');
				}
				$data = array();
				$data['name'] = $post['name'];
				$data['email']    = $post['email'];
				$data['password'] = $post['password'];
				$token = $this->save_member_pre($data);

				$maildata = array();
				$maildata['from_name']    = \Config::get('site.member_setting_common.from_name');
				$maildata['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
				$maildata['subject']      = \Config::get('site.member_confirm_register_mail.subject');
				$maildata['to_address']   = $post['email'];
				$maildata['to_name']      = $post['name'];
				$maildata['password']     = $post['password'];
				$maildata['token']        = $token;
				$this->send_confirm_register_mail($maildata);

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
			catch(Exception $e)
			{
				Session::set_flash('error', $e->getMessage());
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
				->add_rule('min_length', 6)
				->add_rule('max_length', 20)
				->add_rule('match_value', $member_pre->password);
			$val->set_message('match_value', 'パスワードが正しくありません。');
			$val->add('token', '', array('type'=>'hidden'))
				->add_rule('required');

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
					$maildata['from_name']    = \Config::get('site.member_setting_common.from_name');
					$maildata['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
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
				catch(Auth\SimpleUserUpdateException $e)
				{
					Session::set_flash('error', 'そのアドレスは登録できません');
				}
				catch(Exception $e)
				{
					Session::set_flash('error', '登録にに失敗しました。');
				}
			}
			else
			{
				Session::set_flash('error', $val->show_errors());
			}
		}

		$this->set_title_and_breadcrumbs('メンバー登録確認', array('member/signup' => Config::get('term.signup')));
		$data = array('val' => $val, 'member_pre' => $member_pre);
		$this->template->content = View::forge('member/register', $data);
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
		$this->set_title_and_breadcrumbs(Config::get('term.member_leave'), array('/member/setting/' => '設定変更'), $this->u);
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

		$auth = Auth::instance();
		if ($val->run() && $auth->check_password())
		{
			$this->set_title_and_breadcrumbs(
				Config::get('term.member_leave').'確認',
				array('/member/setting/' => '設定変更', '/member/leave/' => Config::get('term.member_leave')),
				$this->u
			);
			$this->template->content = View::forge('member/leave_confirm', array('input' => $val->validated()));
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
			$data['from_name']    = \Config::get('site.member_leave_mail.from_name');
			$data['from_address'] = \Config::get('site.member_leave_mail.from_mail_address');
			$data['subject']      = \Config::get('site.member_leave_mail.subject');

			$data['body'] = <<< END
{$data['to_name']} 様

退会が完了しました。
END;

			try
			{
				$this->delete_user($this->u->id);
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
	 * Mmeber resend password
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
		$this->set_title_and_breadcrumbs('パスワードの再設定');
		$this->template->content = View::forge('member/resend_password');
		$this->template->content->set_safe('html_form', $form->build('/member/confirm_reset_password'));// form の action に入る
	}

	/**
	 * Confirm resend password
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm_reset_password()
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

		$message = 'パスワードのリセット方法をメールで送信しました。';
		if (!$member_auth = Model_MemberAuth::find()->where('email', $post['email'])->related('member')->get_one())
		{
			Session::set_flash('message', $message);
			Response::redirect('site/login');
			return;
		}

		try
		{
			$data = array();
			$data['member_id'] = $member_auth->member_id;
			$data['email']     = $post['email'];

			$maildata = array();
			$maildata['to_name']      = $member_auth->member->name;
			$maildata['to_address']   = $post['email'];
			$maildata['from_name']    = \Config::get('site.member_setting_common.from_name');
			$maildata['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
			$maildata['subject']      = \Config::get('site.member_resend_password.subject');
			$maildata['token']        = $this->save_member_password_pre($data);
			$this->send_confirm_reset_password_mail($maildata);

			Session::set_flash('message', $message);
			Response::redirect('site/login');
		}
		catch(EmailValidationFailedException $e)
		{
			$this->display_error('パスワードのリセット: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
		}
		catch(EmailSendingFailedException $e)
		{
			$this->display_error('パスワードのリセット: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
		}
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

		$member_password_pre = Model_MemberPasswordPre::find()->where('token', Input::param('token'))->related('member')->get_one();
		if (!$member_password_pre || $member_password_pre->created_at < date('Y-m-d H:i:s', strtotime('-1 day')))
		{
			$this->display_error('メンバー登録: 不正なURL');
			return;
		}

		$form = $this->form_reset_password();
		$val  = $form->validation();

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$auth = Auth::instance();

			if ($val->run())
			{
				$post = $val->validated();
				try
				{
					if (!$auth->change_password_simple($member_password_pre->member_id, $post['password']))
					{
						throw new Exception('change password error.');
					}

					$maildata = array();
					$maildata['from_name']    = \Config::get('site.member_setting_common.from_name');
					$maildata['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
					$maildata['subject']      = \Config::get('site.member_reset_password.subject');
					$maildata['to_address']   = $member_password_pre->email;
					$maildata['to_name']      = $member_password_pre->member->name;
					$maildata['password']    = $post['password'];
					$this->send_reset_password_mail($maildata);

					// 仮登録情報の削除
					$member_password_pre->delete();

					$auth->login($member_password_pre->email, $post['password']);
					Session::set_flash('message', 'パスワードを登録しました。');
					Response::redirect('member');
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
				catch(Auth\SimpleUserUpdateException $e)
				{
					Session::set_flash('error', 'パスワードの登録に失敗しました。');
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
			}
		}

		$this->set_title_and_breadcrumbs('パスワードの再登録');
		$data = array('val' => $val, 'member_password_pre' => $member_password_pre);
		$this->template->content = View::forge('member/reset_password', $data);
		$this->template->content->set_safe('html_form', $form->build('/member/reset_password'));// form の action に入る
	}

	public function form()
	{
		$form = Fieldset::forge('confirm_register');

		$form->add('name', '名前')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('max_length', 50);

		$form->add('email', 'メールアドレス')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('valid_email');

		$form->add('password', 'パスワード', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20);

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Util_security::get_csrf()));

		return $form;
	}

	public function form_leave()
	{
		$form = Site_Util::get_form_instance('leave');
		$form->add('password', 'パスワード', array('type'=>'password', 'class' => 'span6'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20);

		$form->add('submit', '', array('type'=>'submit', 'value' => '確認', 'class' => 'btn'));

		return $form;
	}

	public function form_resend_password()
	{
		$form = Fieldset::forge('resend_password', array('class' => 'form-horizontal'));

		$form->add('email', 'メールアドレス')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('valid_email');

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Util_security::get_csrf()));

		return $form;
	}

	public function form_reset_password()
	{
		$form = Fieldset::forge('reset_password');

		$form->add('password', '新しいパスワード', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20);

		$form->add('password_confirm', '新しいパスワード(確認用)', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('match_field', 'password');

		$form->add('token', '', array('type'=>'hidden', 'value' => Input::param('token')))
			->add_rule('required');

		$form->add('submit', '', array('type'=>'submit', 'value' => '変更', 'class' => 'btn'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Util_security::get_csrf()));

		return $form;
	}

	private function check_token()
	{
		if ($member_pre = Model_MemberPre::find()->where('token', Input::param('token'))->get_one())
		{
			return $member_pre;
		}

		return false;
	}

	protected function delete_user($member_id)
	{
		$auth = Auth::instance();
		return $auth->delete_user($member_id) && $auth->logout();
	}

	private function save_member_pre($data)
	{
		$member_pre = new Model_MemberPre();
		$member_pre->name = $data['name'];
		$member_pre->email = $data['email'];
		$member_pre->password = $data['password'];
		$member_pre->token = Util_toolkit::create_hash();
		$member_pre->save();

		return $member_pre->token;
	}

	private function save_member_password_pre($data)
	{
		$member_password_pre = Model_MemberPasswordPre::find()->where('member_id', $data['member_id'])->get_one();
		if (!$member_password_pre) $member_password_pre = new Model_MemberPasswordPre;

		$member_password_pre->member_id = $data['member_id'];
		$member_password_pre->email  = $data['email'];
		$member_password_pre->token     = Util_toolkit::create_hash();
		$member_password_pre->save();

		return $member_password_pre->token;
	}

	private function send_confirm_register_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$register_url = sprintf('%s?token=%s', Uri::create('member/register'), $data['token']);
		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
こんにちは、{$data['to_name']}さん

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

		$data['body'] = <<< END
メンバー登録が完了しました。

====================
お名前: {$data['to_name']}
メールアドレス: {$data['to_address']}
パスワード: {$data['password']}
====================

END;

		Util_toolkit::sendmail($data);
	}

	private function send_confirm_reset_password_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$register_url = sprintf('%s?token=%s', uri::create('member/reset_password'), $data['token']);
		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
こんにちは、{$data['to_name']}さん

{$site_name} は、あなたのアカウントのパスワードをリセットするように依頼を受けました。

パスワードをリセットしたい場合、下記のリンクをクリックしてください (もしくは、URLをコピペしてブラウザに入力してください)。
{$register_url}

パスワードをリセットしたくない場合は、このメッセージを無視してください。 パスワードはリセットされません。

END;

		util_toolkit::sendmail($data);
	}

	private function send_reset_password_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
{$data['to_name']} さん

パスワードを再登録しました。

================================
新しいパスワード: {$data['password']}
================================

END;

		util_toolkit::sendmail($data);
	}
}
