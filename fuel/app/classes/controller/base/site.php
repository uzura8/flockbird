<?php

class Controller_Base_Site extends Controller_Base
{
	protected $auth_driver = 'UzuraAuth';
	protected $after_auth_uri = 'member';
	protected $member_config;
	protected $notification_counts = array();

	public function before()
	{
		parent::before();
		if (!IS_ADMIN && Auth::check())
		{
			$this->set_notification_count();
			$this->set_current_member_config();
		}
	}

	protected function get_current_user()
	{
		return $this->auth_instance->get_member();
	}

	protected function set_current_member_config()
	{
		$this->member_config = Site_Member::get_member_config_with_default_value($this->u->id);
		View::set_global('member_config', $this->member_config);
	}

	protected function check_auth_and_is_mypage($member_id = 0, $is_api = false)
	{
		$is_mypage = false;
		$access_from = 'guest';
		$member    = null;
		$member_id = (int)$member_id;

		if (!$member_id)
		{
			$this->check_auth_and_redirect(false);

			$is_mypage = true;
			$member = $this->u;
			$access_from = 'self';
		}
		elseif ($this->check_is_mypage($member_id))
		{
			$is_mypage = true;
			$member = $this->u;
			$access_from = 'self';
		}
		else
		{
			$member = Model_Member::check_authority($member_id);
			if (Auth::check())
			{
				$access_from = 'member';
				if (Model_MemberRelation::check_relation('friend', $this->u->id, $member_id))
				{
					$access_from = 'friend';
				}
			}
		}

		return array($is_mypage, $member, $access_from);
	}

	protected function check_is_mypage($member_id)
	{
		return (IS_AUTH && $this->u->id && $member_id == $this->u->id);
	}

	protected static function get_breadcrumbs($title_name = '', $middle_breadcrumbs = array(), $member_obj = null, $is_mypage = false, $module = null)
	{
		$breadcrumbs = array('/' => term('page.top'));
		if ($member_obj)
		{
			if ($is_mypage)
			{
				$breadcrumbs['/member'] = term('page.myhome');
				if ($module) $breadcrumbs[sprintf('/%s/member/', $module)] = '自分の'.term($module, 'site.list');
			}
			else
			{
				$name = $member_obj->name.'さんのページ';
				$breadcrumbs['/member/'.$member_obj->id] = $name;
				if ($module)
				{
					$key = sprintf('/%s/member/%d', $module, $member_obj->id);
					$breadcrumbs[$key] = term($module, 'site.list');
				}
			}
		}
		if ($middle_breadcrumbs) $breadcrumbs += $middle_breadcrumbs;
		$breadcrumbs[''] = $title_name;

		return $breadcrumbs;
	}

	protected function add_member_filesize_total($size)
	{
		if (!$this->u) throw new Exception('Not authenticated.');

		$this->u->filesize_total += $size;
		$this->u->save();

		return $this->u->filesize_total;
	}

	protected function check_browse_authority($public_flag, $author_member_id = 0)
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
				if (Auth::check() && $author_member_id && $author_member_id == $this->u->id) return true;
				break;
		}

		throw new \HttpForbiddenException;
	}

	protected function set_notification_count()
	{
		if (is_enabled('notice') && Auth::check())
		{
			$this->notification_counts['notice'] = \Notice\Site_Util::get_unread_count($this->u->id);
		}
		View::set_global('notification_counts', $this->notification_counts);
	}

	protected function change_notice_status2read($member_id, $foreign_table, $foreign_id, $type_key = null)
	{
		if ($read_count = \Notice\Site_Util::change_status2read($member_id, $foreign_table, $foreign_id, $type_key))
		{
			$this->set_notification_count();
		}
	}
}
