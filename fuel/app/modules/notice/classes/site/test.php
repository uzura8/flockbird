<?php
namespace Notice;

class Site_Test
{
	public static function check_no_cache4notice_unread($member_id)
	{
		return is_null(\Site_Develop::get_cache(Site_Util::get_unread_count_cache_key($member_id), \Config::get('notice.cache.unreadCount.expir')));
	}
}
