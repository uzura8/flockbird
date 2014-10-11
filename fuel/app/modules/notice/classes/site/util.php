<?php
namespace Notice;

class Site_Util
{
	public static function get_accept_foreign_tables()
	{
		return array(
			'note',
			'album',
			'album_image',
			'timeline',
		);
	}

	public static function get_notice_type($type_key)
	{
		$types = \Config::get('notice.types');
		if (empty($types[$type_key])) throw new \InvalidArgumentException('Parameter is invalid.');

		return $types[$type_key];
	}

	public static function get_notice_body($foreign_table, $type_key)
	{
		return '';
	}

	public static function change_notice_status2unread($foreign_table, $foreign_id, $member_id_to, $member_id_from, $type_key)
	{
		if ($member_id_from == $member_id_to) return;
		if (!\Site_Member::get_config($member_id_to, 'notice_'.$type_key)) return;

		$obj_notice = \Notice\Model_Notice::check_and_create($foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type($type_key));
		\Notice\Model_NoticeMemberFrom::check_and_create($obj_notice->id, $member_id_from);
		\Notice\Model_NoticeStatus::change_status2unread($member_id_to, $obj_notice->id);
		\Notice\Model_MemberWatchContent::check_and_create($member_id_from, $foreign_table, $foreign_id);
		if (\Config::get('notice.cache.unreadCount.isEnabled')) \Notice\Site_Util::delete_unread_count_cache($member_id_to);
	}

	public static function change_status2read($member_id, $foreign_table, $foreign_id, $type_key)
	{
		$notices = Model_Notice::get4foreign_data($foreign_table, $foreign_id, self::get_notice_type($type_key));
		$reduce_num = 0;
		foreach ($notices as $notice)
		{
			if (Model_NoticeStatus::change_status2read($member_id, $notice->id)) $reduce_num++;
		}
		self::delete_unread_count_cache($member_id);

		return $reduce_num;
	}

	public static function get_unread_count($member_id)
	{
		if (!\Config::get('notice.cache.unreadCount.isEnabled'))
		{
			return Model_NoticeStatus::get_unread_count4member_id($member_id);
		}

		return self::get_unread_count_cache($member_id, true);
	}

	public static function get_unread_count_cache_key($member_id)
	{
		return \Config::get('notice.cache.unreadCount.prefix').$member_id;
	}

	public static function get_unread_count_cache($member_id, $is_make_cache = false)
	{
		$cache_key = self::get_unread_count_cache_key($member_id);
		$cache_expir = \Config::get('notice.cache.unreadCount.expir');
		try
		{
			$unread_count = \Cache::get($cache_key, $cache_expir);
		}
		catch (\CacheNotFoundException $e)
		{
			$unread_count = null;
			if ($is_make_cache)
			{
				$unread_count = Model_NoticeStatus::get_unread_count4member_id($member_id);
				\Cache::set($cache_key, $unread_count, $cache_expir);
			}
		}

		return $unread_count;
	}

	public static function delete_unread_count_cache($member_id)
	{
		$cache_key = self::get_unread_count_cache_key($member_id);
		\Cache::delete($cache_key);
	}
}
