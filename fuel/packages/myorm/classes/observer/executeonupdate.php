<?php
namespace MyOrm;

class Observer_ExecuteOnUpdate extends \Orm\Observer
{
	protected $_conditions;
	protected $_check_properties;
	protected $_execute_func;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_check_properties = isset($props['check_properties']) ? $props['check_properties'] : array();
		$this->_execute_func = $props['execute_func'];
	}

	public function after_update(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	private function main(\Orm\Model $obj)
	{
		if (!$this->_check_properties) return;

		$is_changed = false;
		foreach ($this->_check_properties as $prop)
		{
			if (!$obj->is_changed($prop)) continue;
			$is_changed = true;
		}
		if (!$is_changed) return;

		if (!empty($this->_execute_func['params']))
		{
			foreach ($this->_execute_func['params'] as $value_from => $type)
			{
				$params[] = \Site_Model::get_value_for_observer_setting($obj, $value_from, $type);
			}
		}
		call_user_func_array($this->_execute_func['method'], $params);
	}
}
// End of file executeonupdate.php

