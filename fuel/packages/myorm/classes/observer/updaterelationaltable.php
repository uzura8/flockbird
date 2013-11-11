<?php
namespace MyOrm;

class Observer_UpdateRelationalTable extends \Orm\Observer
{
	public static $required_properties = array(
		'key_from',
		'model_to',
		'table_to',
		'key_to',
		'property_to',
		'property_from',
	);
	protected $key_from;
	protected $model_to;
	protected $table_to;
	protected $key_to;
	protected $property_to;
	protected $property_from;
	protected $is_check_updated_at  = false;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		foreach (static::$required_properties as $property)
		{
			if (!isset($property)) throw new \FuelException('Required property is not set : '.$property);
		}
		$this->key_from = $props['key_from'];
		$this->model_to = $props['model_to'];
		$this->table_to = $props['table_to'];
		$this->key_to   = $props['key_to'];
		$this->property_to   = $props['property_to'];
		$this->property_from = $props['property_from'];
		if (isset($props['is_check_updated_at'])) $this->is_check_updated_at = $props['is_check_updated_at'];
	}

	public function after_update(\Orm\Model $obj)
	{
		if (!class_exists($this->model_to))
		{
			throw new \FuelException('Class not found : '.$this->model_to);
		}

		if ($this->is_check_updated_at)
		{
			$check_property = $this->is_check_updated_at['property'];
			if ($obj->updated_at && $obj->$check_property && $obj->updated_at != $obj->$check_property)
			{
				return;
			}
		}

		$model_to = get_real_class($this->model_to);

		return \DB::update($this->table_to)
			->value($this->property_to, $obj->{$this->property_from})
			->where($this->key_to, $obj->{$this->key_from})
			->execute();
	}
}
// End of file updaterelationaltable.php
