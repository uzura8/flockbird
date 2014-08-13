<?php
namespace MyOrm;

class Observer_SortDatetime extends \Orm\Observer
{
	public static $mysql_timestamp = false;
	public static $property = 'sort_datetime';
	protected $_mysql_timestamp;
	protected $_property;
	protected $_check_changed;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_mysql_timestamp  = isset($props['mysql_timestamp']) ? $props['mysql_timestamp'] : static::$mysql_timestamp;
		$this->_property         = isset($props['property']) ? $props['property'] : static::$property;
		if (!empty($props['check_changed'])) $this->_check_changed = (array)$props['check_changed'];
	}

	public function before_update(\Orm\Model $obj)
	{
		if ($this->check_is_updated($obj))
		{
			$obj->{$this->_property} = $this->_mysql_timestamp ? \Date::time()->format('mysql') : \Date::time()->get_timestamp();
		}
	}

	private function check_is_updated($self_obj)
	{
		if (empty($this->_check_changed['check_properties']) && empty($this->_check_changed['ignore_properties']))
		{
			return true;
		}
		if ($this->check_properties_updated($self_obj)) return true;
		if ($this->check_properties_updated_without_ignores($self_obj)) return true;

		return false;
	}

	private function check_properties_updated($self_obj)
	{
		if (empty($this->_check_changed['check_properties'])) return false;

		$check_properties = (array)$this->_check_changed['check_properties'];
		foreach ($check_properties as $key => $property)
		{
			if (is_array($property))
			{
				$conditions = $property;
				$property = $key;
				foreach ($conditions as $condition => $value)
				{
					if (!$self_obj->is_changed($property)) continue;
					if ($condition == 'ignore_property')
					{
						if ($self_obj->is_changed($value)) continue;

						return true;
					}
					if ($condition == 'ignore_value')
					{
						if ($value == 'reduced_num')
						{
							list($before, $after) = \Util_Orm::get_changed_values($self_obj, $property);
							if (preg_match('/`'.$property.'`\s+\-\s+1/', $after)) continue;
							if ($before > $after) continue;
						}

						return true;
					}
				}
			}
			else
			{
				if ($self_obj->is_changed($property)) return true;
			}
		}

		return false;
	}

	private function check_properties_updated_without_ignores($self_obj)
	{
		if (empty($this->_check_changed['ignore_properties'])) return false;

		$ignore_properties = (array)$this->_check_changed['ignore_properties'];
		$all_properties = \Util_Db::get_columns('timeline');
		foreach ($all_properties as $property)
		{
			if (in_array($property, $ignore_properties)) continue;
			if ($self_obj->is_changed($property)) return true;
		}

		return false;
	}
}
// End of file sortdatetime.php
