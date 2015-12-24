<?php
namespace MyOrm;

class Observer_SortDatetime extends \Orm\Observer
{
	public static $mysql_timestamp = true;
	public static $property = 'sort_datetime';
	public static $property_from = 'updated_at';
	protected $_mysql_timestamp;
	protected $_property;
	protected $_property_from;
	protected $_check_properties;
	protected $_ignore_properties;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_mysql_timestamp  = isset($props['mysql_timestamp']) ? $props['mysql_timestamp'] : static::$mysql_timestamp;
		$this->_property         = isset($props['property']) ? $props['property'] : static::$property;
		$this->_property_from    = isset($props['property_from']) ? $props['property_from'] : static::$property_from;
		if (!empty($props['check_changed']))
		{
			$this->_check_properties = isset($props['check_changed']['check_properties']) ? $props['check_changed']['check_properties'] : array();
			$this->_ignore_properties = isset($props['check_changed']['ignore_properties']) ? $props['check_changed']['ignore_properties'] : array();
		}
	}

	public function before_update(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	public function before_save(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	protected function main(\Orm\Model $obj)
	{
		if (\Util_Orm::check_is_updated($obj, $this->_check_properties, $this->_ignore_properties))
		{
			if (!empty($obj->{$this->_property_from}))
			{
				$obj->{$this->_property} = $obj->{$this->_property_from};
			}
			else	
			{
				$obj->{$this->_property} = $this->_mysql_timestamp ? \Date::time()->format('mysql') : \Date::time()->get_timestamp();
			}
		}
	}
}
// End of file sortdatetime.php
