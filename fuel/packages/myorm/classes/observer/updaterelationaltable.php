<?php
namespace MyOrm;

class Observer_UpdateRelationalTable extends \Orm\Observer
{
	protected $_model_to;
	protected $_relations;
	protected $_property_from;
	protected $_property_to;
	protected $_properties_check_changed;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_model_to = $props['model_to'];
		$this->_relations = $props['relations'];
		$this->_property_from = $props['property_from'];
		$this->_property_to = $props['property_to'];
		if (isset($props['properties_check_changed']))
		{
			$this->_properties_check_changed = $props['properties_check_changed'];
		}
	}

	public function after_insert(\Orm\Model $obj)
	{
		if (!class_exists($this->_model_to))
		{
			throw new \FuelException('Class not found : '.$this->_model_to);
		}
		$model_to = get_real_class($this->_model_to);

		$query = $model_to::query();
		foreach ($this->_relations as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				$value = \Site_Model::get_value_for_observer_setting($obj, $value_from, $type);
				$query = $query->where($property_to, $value);
			}
		}
		$models = $query->get();
		foreach ($models as $model)
		{
			$model->{$this->_property_to} = $obj->{$this->_property_from};
			$model->save();
		}
	}

	public function after_update(\Orm\Model $obj)
	{
		if (!$this->check_target_properties_updated($obj)) return;

		if (!class_exists($this->_model_to))
		{
			throw new \FuelException('Class not found : '.$this->_model_to);
		}
		$model_to = get_real_class($this->_model_to);

		$query = $model_to::query();
		foreach ($this->_relations as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				$value = \Site_Model::get_value_for_observer_setting($obj, $value_from, $type);
				$query = $query->where($property_to, $value);
			}
		}
		$models = $query->get();
		foreach ($models as $model)
		{
			$model->{$this->_property_to} = $obj->{$this->_property_from};
			$model->save();
		}
	}

	private function check_target_properties_updated($obj)
	{
		foreach ($this->_properties_check_changed as $property_check_changed)
		{
			if ($obj->is_changed($property_check_changed)) return true;
		}

		return false;
	}
}
// End of file updaterelationaltable.php
