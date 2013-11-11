<?php
namespace MyOrm;

class Observer_UpdateCacheDuplicate extends \Orm\Observer
{
	public static $required_properties = array(
		'key_from',
		'model_to',
		'key_to',
		'properties',
	);
	protected $key_from;
	protected $model_to;
	protected $key_to;
	protected $properties;
	protected $special_properties   = array();
	protected $is_check_updated_at  = false;
	protected $is_update_duplicated = false;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		foreach (static::$required_properties as $property)
		{
			if (!isset($property)) throw new \FuelException('Required property is not set : '.$property);
		}
		$this->key_from   = $props['key_from'];
		$this->model_to   = $props['model_to'];
		$this->key_to     = $props['key_to'];
		$this->properties = $props['properties'];
		if (isset($props['special_properties'])) $this->special_properties = $props['special_properties'];
		if (isset($props['is_check_updated_at'])) $this->is_check_updated_at = $props['is_check_updated_at'];
		if (isset($props['is_update_duplicated'])) $this->is_update_duplicated = $props['is_update_duplicated'];
	}

	public function after_update(\Orm\Model $obj)
	{
		if (!class_exists($this->model_to))
		{
			throw new \FuelException('Class not found : '.$this->model_to);
		}
		$this->model_to = get_real_class($this->model_to);

		if ($this->is_check_updated_at)
		{
			$check_property = $this->is_check_updated_at['property'];
			if ($obj->updated_at != $obj->$check_property)
			{
				return;
			}
		}

		$cache = $this->get_model_obj($obj);
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

	private function get_model_obj($obj)
	{
		$model_obj = null;
		if ($this->is_update_duplicated)
		{
			$cond = array($this->key_to => $obj->{$this->key_from});
			if (isset($this->is_update_duplicated['additional_conditions']))
			{
				$cond[] = $this->is_update_duplicated['additional_conditions'];
			}
			$model_to = $this->model_to;
			$model_obj = $model_to::find('first', array('where' => $cond));
			if ($model_obj && !empty($this->is_update_duplicated['is_insert_new_record']))
			{
				$model_obj->delete();
			}
		}
		if (!$model_obj) $model_obj = new $this->model_to;

		return $model_obj;
	}
}
// End of file updatecacheduplicate.php
