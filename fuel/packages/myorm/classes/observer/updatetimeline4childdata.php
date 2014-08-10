<?php
namespace MyOrm;

// class 名を適切に変更
class Observer_UpdateTimeline4ChildData extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function after_save(\Orm\Model $obj)
	{
		if (!$timeline = \Timeline\Model_Timeline::check_authority($obj->timeline_id)) return;
		if (\Config::get('timeline.articles.cache.is_use'))
		{
			\Timeline\Site_Util::delete_cache($timeline->id, $timeline->type);
		}
	}

	public function after_delete(\Orm\Model $obj)
	{
		if (!$timeline = \Timeline\Model_Timeline::check_authority($obj->timeline_id)) return;

		$is_cache_delete = false;
		if (!\Timeline\Model_TimelineChildData::get4timeline_id($obj->timeline_id))
		{
			$timeline->delete();
			$is_cache_delete = true;
		}
		// check and update timeline public_flag
		elseif($timeline->update_public_flag_with_check_child_data())
		{
			$is_cache_delete = true;
		}

		if (\Config::get('timeline.articles.cache.is_use') && !$is_cache_delete)
		{
			\Timeline\Site_Util::delete_cache($timeline->id, $timeline->type);
		}
	}
}
// End of file updatetimeline4childdata.php
