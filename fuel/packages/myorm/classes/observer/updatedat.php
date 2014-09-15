<?php
namespace MyOrm;

class Observer_UpdatedAt extends \Orm\Observer
{
	public static $mysql_timestamp = true;
	public static $property = 'updated_at';
	public static $property_from = 'created_at';

	protected $_mysql_timestamp;
	protected $_property;
	protected $_property_from;
	protected $_overwrite;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_mysql_timestamp  = isset($props['mysql_timestamp']) ? $props['mysql_timestamp'] : static::$mysql_timestamp;
		$this->_property         = isset($props['property']) ? $props['property'] : static::$property;
		$this->_property_from    = isset($props['property_from']) ? $props['property_from'] : static::$property;
		$this->_overwrite        = isset($props['overwrite']) ? $props['overwrite'] : true;
	}

	public function before_save(\Orm\Model $obj)
	{
		if ($this->_overwrite or empty($obj->{$this->_property}))
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
// End of file updatedat.php
