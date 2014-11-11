<?php
namespace MyOrm;

class Observer_UpdateTimelineCache extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function after_update(\Orm\Model $obj)
	{
		if (!$timeline_caches = \Timeline\Model_TimelineCache::get4timeline_id($obj->id)) return;

		foreach ($timeline_caches as $timeline_cache)
		{
			// is_follow record のみ timeline_cache.id の付け直し
			if ($obj->is_changed('sort_datetime') && $timeline_cache->is_follow)
			{
				$timeline_cache->delete();
				$timeline_cache->save();
			}
			if ($obj->is_changed('public_flag')) $timeline_cache->public_flag = $obj->public_flag;
			if ($obj->is_changed('comment_count')) $timeline_cache->comment_count = $obj->comment_count;
			if ($obj->is_changed('like_count')) $timeline_cache->like_count = $obj->like_count;
			if ($obj->is_changed('importance_level')) $timeline_cache->importance_level = $obj->importance_level;
			$timeline_cache->save();
		}
	}
}
// End of file updatetimelinecache.php
