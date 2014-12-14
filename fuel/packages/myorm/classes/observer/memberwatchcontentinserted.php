<?php
namespace MyOrm;

class Observer_MemberWatchContentInserted extends \Orm\Observer
{
	public function after_insert(\Notice\Model_MemberWatchContent $obj)
	{
		if (is_enabled('timeline'))
		{
			if (!$timelines = \Notice\Site_Model::get_timelines4foreign_table_and_id($obj->foreign_table, $obj->foreign_id)) return false;
			foreach ($timelines as $timeline)
			{
				$member_follow_timeline = \Timeline\Model_MemberFollowTimeline::check_and_create($timeline->id, $obj->member_id);
			}
		}
	}
}
// End of file memberwatchcontentinserted.php
