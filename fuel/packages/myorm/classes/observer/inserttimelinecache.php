<?php
namespace MyOrm;

class Observer_InsertTimelineCache extends \Orm\Observer
{
	public static $properties = array();
	protected $_properties;
	protected $cache;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_properties = isset($props['properties']) ? $props['properties'] : static::$properties;
	}

	public function after_insert(\Orm\Model $obj)
	{
		$this->cache = new \Timeline\Model_TimelineCache;
		$this->set_properties($obj);
		$this->cache->comment_count = 0;
		$this->cache->save();

		$this->cache = new \Timeline\Model_TimelineCache;
		$this->set_properties($obj);
		$this->cache->is_follow = 1;
		$this->cache->comment_count = 0;
		$this->cache->save();
	}

	protected function set_properties($obj)
	{
		foreach ($this->_properties as $key => $value)
		{
			$property_from = $value;
			$property_to   = $value;
			if (is_string($key) && !empty($key)) $property_to = $key;

			$this->cache->{$property_to} = $obj->{$property_from};
		}
	}
}
// End of file inserttimelinecache.php
