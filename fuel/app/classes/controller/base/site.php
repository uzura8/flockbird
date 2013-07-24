<?php

class Controller_Base_Site extends Controller_Base
{

	public function before()
	{
		parent::before();
	}

	protected function check_not_auth_action($is_api = false)
	{
		$action = $is_api ? sprintf('%s_%s', Str::lower(Request::main()->get_method()), Request::active()->action) : Request::active()->action;
		return in_array($action, $this->check_not_auth_action);
	}

	protected function auth_check($is_api = false, $redirect_uri = '', $is_check_not_auth_action = true)
	{
		if ($is_check_not_auth_action && $this->check_not_auth_action($is_api)) return true;
		if (Auth::check()) return true;

		if ($is_api) return false;

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
				$this->auth_check(false, '', false);
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

	protected function add_member_filesize_total($size)
	{
		if (!$this->u) throw new Exception('Not authenticated.');

		$this->u->filesize_total += $size;
		$this->u->save();

		return $this->u->filesize_total;
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
