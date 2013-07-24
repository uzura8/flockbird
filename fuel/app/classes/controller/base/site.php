<?php

class Controller_Base_Site extends Controller_Base
{

	public function before()
	{
		parent::before();
	}

	protected function check_not_auth_action()
	{
		return in_array(Request::active()->action, $this->check_not_auth_action);
	}

	protected function auth_check($is_redirect_no_auth = true, $redirect_uri = '', $is_check_not_auth_action = true)
	{
		if ($is_check_not_auth_action && $this->check_not_auth_action()) return;

		if (Auth::check()) return true;
		if (!$is_redirect_no_auth) return false;

		if (!$redirect_uri) $redirect_uri = Site_Util::get_login_page_uri();
		Session::set_flash('destination', urlencode(Input::server('REQUEST_URI')));
		Response::redirect($redirect_uri);
	}

	protected function set_current_user()
	{
		$auth = Auth::instance();
		$this->u = Auth::check() ? $auth->get_member() : null;

		View::set_global('u', $this->u);
	}

	protected function check_auth_and_is_mypage($member_id = 0, $is_api = false)
	{
		$is_mypage = false;
		$member    = null;

		if (!$member_id)
		{
			if ($is_api)
			{
				$this->auth_check_api();
			}
			else
			{
				$this->auth_check(true, '', false);
			}
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

	protected function check_is_mypage($member_id)
	{
		return (Auth::check() && $member_id == $this->u->id);
	}

	protected function set_title_and_breadcrumbs($title = '', $middle_breadcrumbs = array(), $member_obj = null, $module = null)
	{
		if ($title) $this->template->title = $title;
		$this->template->header_title = site_title($title);

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
		$breadcrumbs[''] = $title;
		$this->template->breadcrumbs = $breadcrumbs;
	}

	protected function check_public_flag($public_flag, $member_id)
	{
		switch ($public_flag)
		{
			case PRJ_PUBLIC_FLAG_ALL:
				return true;
				break;
			case PRJ_PUBLIC_FLAG_MEMBER:
				if (Auth::check()) return true;
				break;
			//case PRJ_PUBLIC_FLAG_FRIEND:
			//	break;
			case PRJ_PUBLIC_FLAG_PRIVATE:
			default :
				if (Auth::check() && $member_id == $this->u->id) return true;
				break;
		}

		throw new \HttpForbiddenException;
	}
}
