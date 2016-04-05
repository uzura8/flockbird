<?php
namespace Notice;

class Site_View
{
	public static function get_notice_content_uri($foreign_table, $foreign_id, $parent_table, $parent_id)
	{
		if (!$parent_table) $parent_table = $foreign_table;
		if (!$parent_id) $parent_id = $foreign_id;

		return sprintf('%s/%d', static::get_notice_content_uri_middle_path($parent_table), $parent_id);
	}

	public static function get_notice_content_uri_middle_path($foreign_table)
	{
		switch ($foreign_table)
		{
			case 'timeline':
			case 'thread':
			case 'note':
			case 'album':
			case 'member':
				return $foreign_table;
			case 'album_image':
				return 'album/image';
			case 'member_relation':
				return 'member';
		}
		return '';
	}

	public static function convert_notice_foreign_table($foreign_table)
	{
		$is_comment = false;
		if (preg_match('/^([A-Za-z0-9_]+)_comment$/', $foreign_table, $matches))
		{
			$is_comment = true;
			$foreign_table = $matches[1];
		}
		switch ($foreign_table)
		{
			case 'timeline':
			case 'thread':
			case 'note':
			case 'album':
			case 'album_image':
				$suffix = ($is_comment) ? term('form.comment') : '';
				return term($foreign_table).$suffix;
		}
		return '';
	}

	public static function convert_notice_action($foreign_table, $type)
	{
		switch ($type)
		{
			case '6':
				return sprintf('あなた宛に%sを投稿しました。', static::convert_notice_foreign_table($foreign_table));
			case '7':
				return sprintf('あなた宛に%s%sを投稿しました。', static::convert_notice_foreign_table($foreign_table), term('form.comment'));
			case '8':
				return sprintf('あなたを%sしました。', term('follow'));
		}
		return sprintf('%sに%sしました。', static::convert_notice_foreign_table($foreign_table), static::convert_notice_type($foreign_table, $type));
	}

	public static function convert_notice_type($foreign_table, $type)
	{
		switch ($type)
		{
			case '3':
				return term('form.comment');
			case '4':
				return term('form.like');
			case '5':
				if ($foreign_table == 'album') return term('form.add_picture');
				return term('form.add');
		}
		return '';
	}

	public static function get_action_members($members, $member_count)
	{
		$action_members = '';
		foreach ($members as $member)
		{
			if ($action_members) $action_members .= ' と ';
			$action_members .= sprintf('%sさん', $member['name']);
		}
		if ($other_member_count = $member_count - count($members))
		{
			$action_members .= sprintf(' 他%d人', $other_member_count);
		}

		return $action_members;
	}
}
