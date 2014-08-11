<?php
namespace MyOrm;

class Observer_UpdateTimeline extends \Orm\Observer
{
	protected $_check_changed;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		if (!empty($props['check_changed'])) $this->_check_changed = (array)$props['check_changed'];
	}

	public function after_update(\Orm\Model $obj)
	{
		if ($this->check_is_updated($obj))
		{
			\Timeline\Site_Util::delete_cache($obj->id, $obj->type);
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
					if ($conditions == 'ignore_property')
					{
						if ($self_obj->is_changed($value)) continue;

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
// End of file updatetimeline.php
