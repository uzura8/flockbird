<?php
namespace Notice;

class Site_Util
{
	public static function get_accept_foreign_tables()
	{
		// must order for child table to be high priority for using delete method.
		return array(
			'album_image_comment',
			'album_image',
			'album',
			'note_comment',
			'note',
			'timeline_comment',
			'timeline',
			'thread_comment',
			'thread',
		);
	}

	public static function get_accept_parent_tables()
	{
		return array(
			'note',
			'album',
			'album_image',
			'timeline',
			'thread',
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

	public static function get_notice_target_member_ids($member_id_to, $member_id_from, $foreign_table, $foreign_id, $type_key)
	{
		$notice_member_ids = \Util_Orm::conv_col2array(\Notice\Model_MemberWatchContent::get4foreign_data($foreign_table, $foreign_id), 'member_id');
		if ($member_id_to && !in_array($member_id_to, $notice_member_ids)) $notice_member_ids[] = $member_id_to;
		$notice_member_ids = \Util_Array::unset_item($member_id_from, $notice_member_ids);

		if ($type_key == 'comment_like') $type_key = 'like';
		if (!in_array($type_key, array('comment', 'like'))) return $notice_member_ids;

		$config_key = \Notice\Form_MemberConfig::get_name($type_key);

		return \Site_Member::get_member_ids4config_value($config_key, 1, $notice_member_ids);
	}

	public static function update_notice_status2unread($member_id_to, $notice_id)
	{
		$is_changed_status = \Notice\Model_NoticeStatus::change_status2unread($member_id_to, $notice_id);
		if (\Site_Notification::check_is_enabled_cahce('notice') && $is_changed_status)
		{
			\Site_Notification::delete_unread_count_cache('notice', $member_id_to);
		}

		return $is_changed_status;
	}

	public static function regiser_watch_content($member_id, $foreign_table, $foreign_id, $type_key)
	{
		if (!self::check_is_watch_target_content4type_key($member_id, $type_key)) return;

		return \Notice\Model_MemberWatchContent::check_and_create($member_id, $foreign_table, $foreign_id);
	}

	public static function check_is_watch_target_content4type_key($member_id, $type_key)
	{
		return (bool)\Site_Member::get_config($member_id, self::get_member_config_name_for_watch_content($type_key));
	}

	public static function get_member_config_name_for_watch_content($type_key)
	{
		$prefix = 'notice_isWatchContent';
		switch ($type_key)
		{
			case 'comment':
				return $prefix.'Commented';
				break;
			case 'like':
				return $prefix.'Liked';
		}

		throw new \InvalidArgumentException('Parameter is invalid.');
	}

	public static function change_status2read($member_id, $foreign_table, $foreign_id, $type_keys = null)
	{
		$reduce_num = 0;

		$notices = Model_Notice::get4foreign_data($foreign_table, $foreign_id, self::convert_type_keys2types($type_keys));
		foreach ($notices as $notice)
		{
			if (Model_NoticeStatus::change_status2read($member_id, $notice->id)) $reduce_num++;
		}

		$notices = Model_Notice::get4parent_data($foreign_table, $foreign_id);
		foreach ($notices as $notice)
		{
			if (Model_NoticeStatus::change_status2read($member_id, $notice->id)) $reduce_num++;
		}

		\Site_Notification::delete_unread_count_cache('notice', $member_id);

		return $reduce_num;
	}

	protected static function convert_type_keys2types($type_keys = null)
	{
		$types = array();
		if ($type_keys) return $types;

		if (!is_array($type_keys)) $type_keys = (array)$type_keys;
		foreach ($type_keys as $type_key)
		{
			$types[] = self::get_notice_type($type_key);
		}

		return $types;
	}

	public static function get_match_pattern2mention()
	{
		$conf = conf('member.name.validation');
		$accept_str = $conf['match_patterns']['basic'];

		return sprintf('/(?<![%s])(@|＠)([%s]{%d,%d})(?![%s])/u', $accept_str, $accept_str, $conf['length']['min'], $conf['length']['max'], $accept_str);
	}

	public static function check_mention_target($foreign_table, $type_key)
	{
		if ($type_key == 'comment') return true;
		if ($foreign_table == 'timeline' && $type_key == 'create') return true;

		return false;
	}
}
