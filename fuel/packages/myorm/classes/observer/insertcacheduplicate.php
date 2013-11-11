<?php
namespace MyOrm;

class Observer_InsertCacheDuplicate extends \Orm\Observer
{
	public static $required_properties = array(
		'model_to',
		'properties',
	);
	protected $model_to;
	protected $properties;
	protected $special_properties   = array();

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		foreach (static::$required_properties as $property)
		{
			if (!isset($property)) throw new \FuelException('Required property is not set : '.$property);
		}
		$this->model_to   = $props['model_to'];
		$this->properties = $props['properties'];
		if (isset($props['special_properties'])) $this->special_properties = $props['special_properties'];
	}

	public function after_insert(\Orm\Model $obj)
	{
		if (!class_exists($this->model_to))
		{
			throw new \FuelException('Class not found : '.$this->model_to);
		}
		$model_to = get_real_class($this->model_to);
		$cache = new $this->model_to;

		foreach ($this->properties as $key => $value)
		{
			$property_from = $value;
			$property_to   = $value;
			if (is_string($key) && !empty($key)) $property_to = $key;

			$cache->{$property_to} = $obj->{$property_from};
		}
		foreach ($this->special_properties as $property => $values)
		{
			$value = isset($values['value']) ? $values['value'] : null;
			$cache->{$property} = $value;
		}

		$cache->save();
	}
}
// End of file insertcacheduplicate.php
