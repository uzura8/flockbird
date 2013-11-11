<?php
namespace MyOrm;

class Observer_InsertRelationialTable extends \Orm\Observer
{
	public static $required_properties = array(
		'model_to',
		'properties',
	);
	protected $model_to;
	protected $properties;
	protected $is_check_duplicated  = array();
	protected $additional_records   = array();
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
		if (isset($props['is_check_duplicated'])) $this->is_check_duplicated = $props['is_check_duplicated'];
		if (isset($props['additional_records']))  $this->additional_records  = $props['additional_records'];
		if (isset($props['special_properties']))  $this->special_properties  = $props['special_properties'];
	}

	public function after_insert(\Orm\Model $obj)
	{
		if (!class_exists($this->model_to))
		{
			throw new \FuelException('Class not found : '.$this->model_to);
		}
		$this->model_to = get_real_class($this->model_to);

		$this->save_single_record($obj, $this->properties, $this->special_properties);
		if ($this->additional_records) $this->save_multiple_records($obj, $this->additional_records);
	}

	private function save_single_record($obj, $properties, $additional_properties = array())
	{
		if (!$this->check_value($obj, $properties)) return false;
		$relational_table = $this->get_model_obj($obj);

		foreach ($properties as $key => $value)
		{
			$property_from = $value;
			$property_to   = $value;
			if (is_string($key) && !empty($key)) $property_to = $key;

			$relational_table->{$property_to} = $obj->{$property_from};
		}

		if ($additional_properties)
		{
			foreach ($additional_properties as $property => $values)
			{
				$value = isset($values['value']) ? $values['value'] : null;
				$relational_table->{$property} = $value;
			}
		}

		$relational_table->save();
	}

	private function save_multiple_records($obj, $properties_list)
	{
		foreach ($properties_list as $properties)
		{
			$this->save_single_record($obj, $properties);
		}
	}

	private function check_value($obj, $properties)
	{
		foreach ($properties as $key => $value)
		{
			$property_from = $value;
			$property_to   = $value;
			if (is_string($key) && !empty($key)) $property_to = $key;

			if (is_null($obj->{$property_from})) return false;
		}

		return true;
	}

	private function get_model_obj($obj)
	{
		$model_obj = null;
		if ($this->is_check_duplicated)
		{
			if (isset($this->is_check_duplicated['conditions']))
			{
				$cond = array();
				foreach ($this->is_check_duplicated['conditions'] as $key => $value)
				{
					$property_from = $value;
					$property_to   = $value;
					if (is_string($key) && !empty($key)) $property_to = $key;

					$cond[$property_to] = $obj->{$property_from};
				}
			}
			$model_to = $this->model_to;
			$model_obj = $model_to::find('first', array('where' => $cond));
		}
		if (!$model_obj) $model_obj = new $this->model_to;

		return $model_obj;
	}
}
// End of file insertrelationaltable.php
