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

	public static function convert_notice_message($foreign_table, $type, $subject, $lang = null)
	{
		if (! $lang) $lang = get_default_lang();
		$label = static::convert_notice_foreign_table($foreign_table);

		switch ($type)
		{
			case '3':
			case '4':
				$lang_item = sprintf('message_%s_from_to', $type == '3' ? 'comment' : 'like');
				return \Lang::get($lang_item, array(
					'subject' => $subject,
					'object'  => $label,
				), '', $lang);

			case '5':
				if ($foreign_table == 'album')
				{
					return \Lang::get('message_add_for_from_to', array(
						'subject' => $subject,
						'object'  => $label,
						'label'  => t('site.picture', null, $lang),
					), '', $lang);
				}
				return \Lang::get('message_add_from_to', array(
					'subject' => $subject,
					'object'  => t('site.picture', null, $lang),
				), '', $lang);

			case '6':
			case '7':
				$label = static::convert_notice_foreign_table($foreign_table);
				if ($type == 7)
				{
					$delimitter = $lang == 'ja' ? '' : ' ';
					$label .= $delimitter.t('form.comment', null, $lang);
				}
				return \Lang::get('message_post_for_from_to', array(
					'subject' => $subject,
					'object'  => t('common.you', null, $lang),
					'label'   => $label,
				), '', $lang);

			case '8':
				return \Lang::get('member_message_follow_from_to', array(
					'subject' => $subject,
					'object'  => t('common.you', null, $lang),
				), '', $lang);
		}

		return '';
	}

	public static function get_action_members($members, $member_count, $lang = null)
	{
		if (! $lang) $lang = get_default_lang();
		$delimitter      = t('common.delimitter.normal');
		$delimitter_last = t('common.delimitter.last');

		$action_members = '';
		$max = count($members);
		for ($i = 0; $i < $max; $i++)
		{
			if ($action_members)
			{
				if ($i == ($max - 1))
				{
					$action_members .= $delimitter_last;
				}
				elseif ($i)
				{
					$action_members .= $delimitter;
				}
			}
			$action_members .= conv_honorific_name($members[$i]['name'], $lang);
		}

		if ($other_member_count = $member_count - count($members))
		{
			$action_members .= ' '.t('other_members_count', array('num' => $other_member_count));
		}

		return $action_members;
	}
}
