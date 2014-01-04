<?php
namespace MyOrm;

// class 名を適切に変更
class Observer_DeleteOrUpdateTimeline4ChildData extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function after_delete(\Orm\Model $obj)
	{
		if (!$timeline = \Timeline\Model_Timeline::check_authority($obj->timeline_id)) return;

		if (!\Timeline\Model_TimelineChildData::get4timeline_id($obj->timeline_id))
		{
			$timeline->delete();
		}
		else
		{
			// check and update timeline public_flag
			$timeline->update_public_flag_with_check_child_data();
		}
	}
}
// End of file deleteorupdatetimeline4childdata.php
