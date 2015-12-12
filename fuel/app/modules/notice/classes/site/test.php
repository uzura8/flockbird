<?php
namespace Notice;

class Site_Test
{
	public static function check_no_cache4notice_unread($member_id)
	{
		return is_null(\Site_Develop::get_cache(\Site_Notification::get_unread_count_cache_key('notice', $member_id), \Site_Notification::get_cahce_expire()));
	}
}
