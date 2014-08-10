<?php
namespace MyOrm;

class Observer_ExecuteToRelations extends \Orm\Observer
{
	protected $_relations;
	protected $_model_to;
	protected $_conditions;
	protected $_execute_func;
	protected $_properties_check_changed;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_relations = (array)$props['relations'];
	}

	public function before_insert(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function after_insert(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function before_update(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function after_update(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function before_save(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function after_save(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function before_delete(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function after_delete(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	private function main(\Orm\Model $obj)
	{
		if (!$this->_relations) return;
		foreach ($this->_relations as $props)
		{
			$this->_model_to = $props['model_to'];
			$this->_conditions = $props['conditions'];
			$this->_execute_func = $props['execute_func'];
			if (isset($props['properties_check_changed']))
			{
				$this->_properties_check_changed = $props['properties_check_changed'];
				if (!$this->check_target_properties_updated($obj)) continue;
			}
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
			$params = array();
			if (!empty($this->_execute_func['params']))
			{
				foreach ($this->_execute_func['params'] as $value_from => $type)
				{
					$params[] = \Site_Model::get_value_for_observer_setting($model, $value_from, $type);
				}
			}
			call_user_func_array($this->_execute_func['method'], $params);
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
// End of file executetorelations.php
