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
}
