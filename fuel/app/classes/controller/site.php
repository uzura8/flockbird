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
		$this->u = Auth::check() ? Model_Member::find($auth->get_member_id(), array('rows_limit' => 1, 'related' => 'memberauth')) : null;

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

	public function action_login()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		$val = Validation::forge();

		if (Input::method() == 'POST')
		{
			$val->add('email', 'メールアドレス')->add_rule('required');
			$val->add('password', 'パスワード')->add_rule('required');
			$val->add('destination');

			if ($val->run())
			{
				$auth = Auth::instance();

				// check the credentials. This assumes that you have the previous table created
				if (Auth::check() or $auth->login(Input::post('email'), Input::post('password')))
				{
					// credentials ok, go right in
					Session::set_flash('message', 'ログインしました');

					if (Input::post('destination')) Response::redirect(urldecode(Input::post('destination')));
					Response::redirect('member');
				}
				else
				{
					Session::set_flash('error', 'ログインに失敗しました');
				}
			}
		}

		$this->set_title_and_breadcrumbs('ログイン');
		$destination = Session::get_flash('destination') ?: Input::post('destination', '');
		$this->template->content = View::forge('site/login', array('val' => $val, 'destination' => $destination));
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

		$breadcrumbs = array(Config::get('site.term.toppage') => '/');
		if ($member_obj)
		{
			if ($this->check_is_mypage($member_obj->id))
			{
				$breadcrumbs[Config::get('site.term.myhome')] = '/member';
				if ($module)
				{
					$key = '自分の'.\Config::get($module.'.term.'.$module).'一覧';
					$breadcrumbs[$key] =  sprintf('/%s/member/', $module);
				}
			}
			else
			{
				$prefix = $member_obj->name.'さんの';
				$key = $prefix.Config::get('site.term.profile');
				$breadcrumbs[$key] = '/member/'.$member_obj->id;
				if ($module)
				{
					$key = $prefix.\Config::get($module.'.term.'.$module).'一覧';
					$breadcrumbs[$key] =  sprintf('/%s/member/%d', $module, $member_obj->id);
				}
			}
		}
		if ($middle_breadcrumbs) $breadcrumbs += $middle_breadcrumbs;
		$breadcrumbs[$title] = '';
		$this->template->breadcrumbs = $breadcrumbs;
	}

	protected function check_is_mypage($member_id)
	{
		return (Auth::check() && $member_id == $this->u->id);
	}
}
