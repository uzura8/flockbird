<?php
namespace MyOrm;

class Observer_CopyValue extends \Orm\Observer
{
	public static $property_to   = '';
	public static $property_from = '';
	protected $_property_to;
	protected $_property_from;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_property_from = isset($props['property_from']) ? $props['property_from'] : static::$property_from;
		$this->_property_to   = isset($props['property_to']) ? $props['property_to'] : static::$property_to;
	}

	public function before_insert(\Orm\Model $obj)
	{
		$obj->{$this->_property_to} = $obj->{$this->_property_from};
	}
}
// End of file copyvalue.php
