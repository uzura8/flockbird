<?php
namespace MyOrm;

class Observer_ExecuteOnCreate extends \Orm\Observer
{
	protected $_execute_func;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_execute_func = $props['execute_func'];
	}

	public function after_insert(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	public function after_save(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	private function main(\Orm\Model $obj)
	{
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
// End of file executeoncreate.php

