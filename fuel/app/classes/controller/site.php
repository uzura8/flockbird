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
class Controller_Site extends Controller_Base_Site
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
		$this->template->title = PRJ_SITE_NAME;
		$this->template->breadcrumbs = array();
	}

	protected function display_error($message_display = '', $messsage_log = '', $action = 'error/500', $status = 500)
	{
		if ($messsage_log) \Log::error($messsage_log);
		$this->template->title = ($message_display) ? $message_display : 'Error';
		$this->template->header_title = site_title($this->template->title);
		$this->template->content = View::forge($action);
		if ($status) $this->response->status = $status;
	}

	protected function set_title_and_breadcrumbs($title = array(), $middle_breadcrumbs = array(), $member_obj = null, $module = null, $info = array())
	{
		if ($title)
		{
			if (is_array($title))
			{
				$title_name  = !empty($title['name'])  ? $title['name'] : '';
				$title_label = !empty($title['label']) ? $title['label'] : array();
			}
			else
			{
				$title_name  = $title;
				$title_label = array();
			}
			$this->template->title = View::forge('_parts/page_title', array('name' => $title_name, 'label' => $title_label));
		}
		$this->template->header_title = site_title($title_name);

		if ($info) $this->template->header_info = View::forge('_parts/information', $info);

		$breadcrumbs = array('/' => Config::get('term.toppage'));
		if ($member_obj)
		{
			if ($this->check_is_mypage($member_obj->id))
			{
				$breadcrumbs['/member'] = Config::get('term.myhome');
				if ($module)
				{
					$breadcrumbs[sprintf('/%s/member/', $module)] = '自分の'.\Config::get('term.'.$module).'一覧';
				}
			}
			else
			{
				$prefix = $member_obj->name.'さんの';
				$name = $prefix.Config::get('term.profile');
				$breadcrumbs['/member/'.$member_obj->id] = $name;
				if ($module)
				{
					$key = sprintf('/%s/member/%d', $module, $member_obj->id);
					$breadcrumbs[$key] = $prefix.\Config::get('term.'.$module).'一覧';
				}
			}
		}
		if ($middle_breadcrumbs) $breadcrumbs += $middle_breadcrumbs;
		$breadcrumbs[''] = $title_name;
		$this->template->breadcrumbs = $breadcrumbs;
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
					$this->login_failed(false);
				}
			}
		}

		$this->set_title_and_breadcrumbs('ログイン');
		$this->template->content = View::forge('site/_parts/login', array('val' => $val, 'destination' => $destination));
	}

	protected function login($member_id)
	{
		$auth = Auth::instance();
		$auth->logout();
		if (!$auth->force_login($member_id))
		{
			throw new FuelException('Member login failed.');
		}

		return true;
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

	protected function login_failed($is_redirect = true)
	{
		Session::set_flash('error', 'ログインに失敗しました');
		if ($is_redirect) Response::redirect('site/login');
	}
}
