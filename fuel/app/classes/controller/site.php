<?php

/**
 * The Site Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Site extends Controller_Base
{
	protected $check_not_auth_action = array(
		'index',
		'login',
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
		$this->set_current_user();

		$this->template->header_keywords = '';
		$this->template->header_description = '';
	}

	private function set_current_user()
	{
		$auth = Auth::instance();
		$this->u = Auth::check() ? $auth->get_member() : null;

		View::set_global('u', $this->u);
	}

	protected function add_member_filesize_total($size)
	{
		if (!$this->u) throw new Exception('Not authenticated.');

		$this->u->filesize_total += $size;
		$this->u->save();

		return $this->u->filesize_total;
	}

	/**
	 * Site index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = PRJ_SITE_NAME.'メインメニュー';
		$this->template->header_title = site_title();

		$this->template->content = View::forge('site/index');
	}

	/**
	 * The login.
	 * 
	 * @access  public
	 * @return  Response or void
	 */
	public function action_login()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		$destination = Session::get_flash('destination') ?: Input::post('destination', '');
		$val = Validation::forge();

		if (Input::method() == 'POST')
		{
			$val->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Util_security::get_csrf()));
			$val->add('email', 'メールアドレス', array('type' => 'email'))->add_rule('required');
			$val->add('password', 'パスワード')->add_rule('required');

			$val = $form->validation();
			if ($val->run())
			{
				Util_security::check_csrf();
				$auth = Auth::instance();

				// check the credentials. This assumes that you have the previous table created
				if (Auth::check() or $auth->login(Input::post('email'), Input::post('password')))
				{
					// does the user want to be remembered?
					if (Input::param('rememberme', false))
					{
						// create the remember-me cookie
						Auth::remember_me();
					}
					else
					{
						// delete the remember-me cookie if present
						Auth::dont_remember_me();
					}

					// credentials ok, go right in
					Session::set_flash('message', 'ログインしました');

					$redirect_uri = urldecode($destination);
					if ($redirect_uri && Util_string::check_uri_for_redilrect($redirect_uri))
					{
						Response::redirect($redirect_uri);
					}
					Response::redirect('member');
				}
				else
				{
					Session::set_flash('error', 'ログインに失敗しました');
				}
			}
		}

		$this->set_title_and_breadcrumbs('ログイン');
		$this->template->content = View::forge('site/_parts/login', array('val' => $val, 'destination' => $destination));
	}

	/**
	 * The logout action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_logout()
	{
		if ($this->u->register_type == 1) Response::redirect('facebook/logout');

		Auth::logout();
		Session::set_flash('message', 'ログアウトしました');
		Response::redirect('site/login');
	}

	protected function check_auth_and_is_mypage($member_id = 0)
	{
		$is_mypage = false;
		$member    = null;

		if (!$member_id)
		{
			$this->auth_check(true, '', false);

			$is_mypage = true;
			$member = $this->u;
		}
		elseif ($this->check_is_mypage($member_id))
		{
			$is_mypage = true;
			$member = $this->u;
		}
		elseif (!$member = Model_Member::check_authority($member_id))
		{
			throw new \HttpNotFoundException;
		}

		return array($is_mypage, $member);
	}

	protected function set_title_and_breadcrumbs($title = '', $middle_breadcrumbs = array(), $member_obj = null, $module = null)
	{
		$this->template->title = $title ?: PRJ_SITE_NAME;
		$this->template->header_title = site_title($title);

		$breadcrumbs = array('/' => Config::get('site.term.toppage'));
		if ($member_obj)
		{
			if ($this->check_is_mypage($member_obj->id))
			{
				$breadcrumbs['/member'] = Config::get('site.term.myhome');
				if ($module)
				{
					$breadcrumbs[sprintf('/%s/member/', $module)] = '自分の'.\Config::get($module.'.term.'.$module).'一覧';
				}
			}
			else
			{
				$prefix = $member_obj->name.'さんの';
				$name = $prefix.Config::get('site.term.profile');
				$breadcrumbs['/member/'.$member_obj->id] = $name;
				if ($module)
				{
					$key = sprintf('/%s/member/%d', $module, $member_obj->id);
					$breadcrumbs[$key] = $prefix.\Config::get($module.'.term.'.$module).'一覧';
				}
			}
		}
		if ($middle_breadcrumbs) $breadcrumbs += $middle_breadcrumbs;
		$breadcrumbs[''] = $title;
		$this->template->breadcrumbs = $breadcrumbs;
	}

	protected function check_is_mypage($member_id)
	{
		return (Auth::check() && $member_id == $this->u->id);
	}
}
