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
			if ($obj->updated_at == $obj->sort_datetime)
			{
				$timeline_cache->sort_datetime = $obj->sort_datetime;
			}
			else
			{
				$timeline_cache->public_flag = $obj->public_flag;
			}
			$timeline_cache->save();
		}
	}
}
// End of file updatetimelinecache.php
