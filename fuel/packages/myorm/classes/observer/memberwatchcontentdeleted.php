<?php
namespace MyOrm;

class Observer_MemberWatchContentDeleted extends \Orm\Observer
{
	public function before_delete(\Notice\Model_MemberWatchContent $obj)
	{
		if (is_enabled('timeline'))
		{
			if (!$timelines = \Notice\Site_Model::get_timelines4foreign_table_and_id($obj->foreign_table, $obj->foreign_id)) return false;
			foreach ($timelines as $timeline)
			{
				$member_follow_timeline = \Timeline\Model_MemberFollowTimeline::get4timeline_id_and_member_id($timeline->id, $obj->member_id);
				$member_follow_timeline->delete();
			}
		}
	}
}
// End of file memberwatchcontentdeleted.php
