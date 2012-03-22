<?php

class Controller_Member extends Controller_Site {

	//public $template = 'admin/template';

	private $check_not_auth_action = array(
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
		$this->template->title = PRJ_SITE_NAME.'マイホーム';
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
		if ( ! \Security::check_token())
		{
			\Log::error(
				'CSRF: '.
				\Input::uri().' '.
				\Input::ip().
				' "'.\Input::user_agent().'"'
			);
			throw new HttpInvalidInputException('Invalid input data');
		}

		$form = $this->form();
		$val = $this->form()->validation();
		$val->add_callable('myvalidation');

		if ($val->run())
		{
			$post = $val->validated();

			$data = array();
			$data['username'] = $post['username'];
			$data['password'] = $post['password'];
			$data['nickname'] = $post['nickname'];
			$data['email']    = $post['email'];

			$maildata['from_name']    = \Config::get('site.member_register_mail.from_name');
			$maildata['from_address'] = \Config::get('site.member_register_mail.from_mail_address');
			$maildata['subject']      = \Config::get('contact.mail_subject');
			$maildata['to_address']    = $post['email'];
			$maildata['to_name'] = (!empty($post['nickname'])) ? $post['nickname'] : $post['username'];

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
			catch(EmailSavingFailedException $e)
			{
				$this->template->title = 'メンバー登録: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email saving error: ' .
					$e->getMessage()
				);
			}
		}
		else
		{
			$form->repopulate();

			$this->template->title = 'メンバー登録: エラー';
			$this->template->breadcrumbs = array(Config::get('site.term.signup') => '/', Config::get('site.term.signup') => '');
			$this->template->content = View::forge('member/signup');
			$this->template->content->set_safe('html_error', $val->show_errors());
			$this->template->content->set_safe('html_form', $form->build('/member/register'));
		}
	}

	private function check_not_auth_action()
	{
		return in_array(Request::active()->action, $this->check_not_auth_action);
	}

	public function form()
	{
		$form = Fieldset::forge();

		$form->add('username', '名前')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('max_length', 20);

		$form->add('password', 'パスワード', array('type'=>'password'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('min_length', 6)
			->add_rule('max_length', 20);

		$form->add('email', 'メールアドレス')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('valid_email');

		$form->add('nickname', 'ニックネーム')
			->add_rule('trim')
			->add_rule('no_controll')
			->add_rule('max_length', 50);

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信'));
		$form->add(Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => Security::fetch_token()));

		return $form;
	}

	public function save($data)
	{
		// create new member
		$auth = Auth::instance();
		if (!$member_id = $auth->create_user($data['username'], $data['password'], $data['email']))
		{
			throw new Exception('create member error.');
		}

		// update for nickname.
		$member = Model_Member::find($member_id);
		$member->nickname = $data['nickname'];
		if (!$member->save())
		{
			throw new Exception('update member error.');
		}
	}
}
