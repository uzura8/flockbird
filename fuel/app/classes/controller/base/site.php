<?php

class Controller_Base_Site extends Controller_Base
{

	public function before()
	{
		parent::before();

		if (!IS_API)
		{
			$this->set_title_and_breadcrumbs(PRJ_SITE_NAME);
			$this->template->header_keywords = '';
			$this->template->header_description = '';
		}
	}

	protected function set_current_user()
	{
		$auth = Auth::instance();
		$this->u = Auth::check() ? $auth->get_member() : null;

		View::set_global('u', $this->u);
	}

	protected function set_title_and_breadcrumbs($title = array(), $middle_breadcrumbs = array(), $member_obj = null, $module = null, $info = array(), $is_no_breadcrumbs = false, $is_no_title = false)
	{
		$title_name = '';
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
			$this->template->title = $is_no_title ? '' : View::forge('_parts/page_title', array('name' => $title_name, 'label' => $title_label));
		}
		$this->template->header_title = $title_name ? site_title($title_name) : '';

		if ($info) $this->template->header_info = View::forge('_parts/information', $info);

		$breadcrumbs = array();
		if (!$is_no_breadcrumbs)
		{
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
		}
		$this->template->breadcrumbs = $breadcrumbs;
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
