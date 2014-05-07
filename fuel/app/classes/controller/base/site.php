<?php

class Controller_Base_Site extends Controller_Base
{
	protected $auth_driver = 'UzuraAuth';
	protected $after_auth_uri = 'member';

	public function before()
	{
		parent::before();
	}

	protected function get_current_user($member_id = null)
	{
		return Model_Member::set_member_config_property_default_value($this->auth_instance->get_member());
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

			return array($is_mypage, $member, $access_from);
		}
		elseif ($this->check_is_mypage($member_id))
		{
			$is_mypage = true;
			$member = $this->u;
			$access_from = 'self';

			return array($is_mypage, $member, $access_from);
		}
		elseif ($this->u && Model_MemberRelation::check_relation('friend', $this->u->id, $member_id))
		{
			$is_mypage = false;
			$access_from = 'friend';

			return array($is_mypage, $member, $access_from);
		}
		elseif (!$member = Model_Member::check_authority($member_id))
		{
			throw new \HttpNotFoundException;
		}

		$is_mypage = false;
		$access_from = IS_AUTH ? 'member' : 'guest';

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
				if ($module) $breadcrumbs[sprintf('/%s/member/', $module)] = '自分の'.\Config::get('term.'.$module).'一覧';
			}
			else
			{
				$name = $member_obj->name.'さんのページ';
				$breadcrumbs['/member/'.$member_obj->id] = $name;
				if ($module)
				{
					$key = sprintf('/%s/member/%d', $module, $member_obj->id);
					$breadcrumbs[$key] = \Config::get('term.'.$module).'一覧';
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
