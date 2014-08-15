<?php
namespace MyOrm;

class Observer_UpdateRelationalTables extends \Orm\Observer
{
	protected $_relations;
	protected $_model_to;
	protected $_conditions;
	protected $_update_properties;
	protected $_properties_check_changed;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_relations = (array)$props['relations'];
	}

	public function before_update(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function after_update(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	private function main(\Orm\Model $obj)
	{
		if (!$this->_relations) return;
		foreach ($this->_relations as $props)
		{
			$this->_update_properties = $props['update_properties'];
			if ($props['is_check_updated'] && !$this->check_is_updated($obj)) continue;

			$this->_model_to = $props['model_to'];
			$this->_conditions = $props['conditions'];
			$this->execute($obj);
		}
	}

	private function execute($obj)
	{
		if (!class_exists($this->_model_to))
		{
			throw new \FuelException('Class not found : '.$this->_model_to);
		}
		$model_to = get_real_class($this->_model_to);
		$query = $model_to::query();
		foreach ($this->_conditions as $property_to => $froms)
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
			foreach ($this->_update_properties as $property_to => $froms)
			{
				if (!is_array($froms))
				{
					$model->{$froms} = $obj->{$froms};
				}
				else
				{
					foreach ($froms as $value_from => $type)
					{
						$value = \Site_Model::get_value_for_observer_setting($obj, $value_from, $type);
						$model->{$property_to} = $value;
					}
				}
			}
			if (!$model->is_changed()) continue;
			$model->save();
		}
	}

	private function check_is_updated($obj)
	{
		$is_changed = false;
		foreach ($this->_update_properties as $property_to => $froms)
		{
			if (!is_array($froms))
			{
				if ($obj->is_changed($froms)) return true;
			}
			else
			{
				foreach ($froms as $value_from => $type)
				{
					if ($type == 'value') return true;
					if ($type == 'property')
					{
						if ($obj->is_changed($value_from)) return true;
					}
				}
			}
		}

		return false;
	}
}
// End of file updaterelationaltables.php
