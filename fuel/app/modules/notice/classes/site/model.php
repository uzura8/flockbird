<?php
namespace Notice;

class Site_Model
{
	public static function get_timelines4foreign_table_and_id($foreign_table, $foreign_id)
	{
		if ($foreign_table == 'timeline')
		{
			if (!$timeline = \Timeline\Model_Timeline::find($foreign_id)) return false;

			return array($timeline->id => $timeline);
		}
		if (!in_array($foreign_table, \Timeline\Site_Util::get_accept_timeline_foreign_tables())) return false;

		return \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids($foreign_table, $foreign_id);
	}

	public static function execut_notice($foreign_table, $foreign_id, $type_key, $member_id_from, $notice_member_ids)
	{
		if (!$notice_member_ids) return;

		$obj_notice = Model_Notice::check_and_create($foreign_table, $foreign_id, Site_Util::get_notice_type($type_key));
		Model_NoticeMemberFrom::check_and_create($obj_notice->id, $member_id_from);
		foreach ($notice_member_ids as $notice_member_id)
		{
			Site_Util::update_notice_status2unread($notice_member_id, $obj_notice->id);
		}
	}

	public static function get_mentioned_member_ids4body($body)
	{
		if (!preg_match_all(Site_Util::get_match_pattern2mention(), $body, $matches, PREG_SET_ORDER)) return array();

		$member_names = array();
		foreach ($matches as $match) $member_names[] = $match[2];

		if (!$members = \Model_Member::query()->where('name', 'in', $member_names)->get()) array();

		return \Util_Orm::conv_col2array($members, 'id');
	}
}
