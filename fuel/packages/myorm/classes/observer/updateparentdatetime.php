<?php
namespace MyOrm;

class Observer_UpdateParentDatetime extends \Orm\Observer
{
	public static $property_from = 'updated_at';
	protected $_model_to;
	protected $_key_from;
	protected $_key_to;
	protected $_property_from;
	protected $_property_to;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_property_from = $props['property_from'];
		$this->_property_from = isset($props['property_from']) ? $props['property_from'] : static::$property_from;
		$this->_property_to = $props['property_to'];
		$this->_key_from = $props['key_from'];
		$this->_key_to   = $props['key_to'];
		$this->_model_to = $props['model_to'];
	}

	public function after_insert(\Orm\Model $obj)
	{
		if (!class_exists($this->_model_to))
		{
			throw new \FuelException('Class not found : '.$this->_model_to);
		}
		$model_to = get_real_class($this->_model_to);
		$model = $model_to::find('first', array('where' => array($this->_key_to => $obj->{$this->_key_from})));
		$model->{$this->_property_to} = $obj->{$this->_property_from};
		$model->save();
	}
}
// End of file updateparentdatetime.php
