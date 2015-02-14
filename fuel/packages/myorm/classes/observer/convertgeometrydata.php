<?php
namespace MyOrm;

class Observer_ConvertGeometryData extends \Orm\Observer
{
	public static $property = 'latlng';
	protected $_property;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_property = isset($props['property']) ? $props['property'] : static::$property;
	}

	public function before_save(\Orm\Model $obj)
	{
		$latlng = $obj->{$this->_property};
		if (!$latlng) return;

		$obj->set($this->_property, \DB::expr('GeomFromText("POINT('.$latlng[0].' '.$latlng[1].')")'));
	}

	public function after_load(\Orm\Model $obj)
	{
		$latlng = $obj->{$this->_property};
		if (!$latlng) return;
		if (!preg_match('/POINT\((.+)\s(.+)\)/', $latlng, $matches)) return;

		$obj->{$this->_property} = array($matches[1], $matches[2]);
	}
}
// End of file convertgeometrydata.php
