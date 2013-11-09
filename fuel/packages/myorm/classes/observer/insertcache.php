<?php
namespace MyOrm;

class Observer_InsertCache extends \Orm\Observer
{
	public static $properties = array();
	public static $model_to   = '';
	protected $_model_to;
	protected $_properties;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_model_to = isset($props['model_to']) ? $props['model_to'] : static::$model_to;
		$this->_properties = isset($props['properties']) ? $props['properties'] : static::$properties;
	}

	public function after_insert(\Orm\Model $obj)
	{
		$cache = new $this->_model_to;
		foreach ($this->_properties as $key => $value)
		{
			$property_from = $value;
			$property_to   = $value;
			if (is_string($key) && !empty($key)) $property_to = $key;

			$cache->{$property_to} = $obj->{$property_from};
		}

		$cache->save();
	}
}
// End of file insertcache.php
