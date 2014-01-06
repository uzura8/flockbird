<?php
namespace MyOrm;

class Observer_UpdateTimelineDatetime extends \Orm\Observer
{
	protected $_model_to;
	protected $_relations;
	protected $_property_from;
	protected $_property_to;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_model_to = $props['model_to'];
		$this->_relations = $props['relations'];
		$this->_property_from = $props['property_from'];
		$this->_property_to = $props['property_to'];
	}

	public function after_insert(\Orm\Model $obj)
	{
		if (!class_exists($this->_model_to))
		{
			throw new \FuelException('Class not found : '.$this->_model_to);
		}
		$model_to = get_real_class($this->_model_to);

		$query = $model_to::query();
		foreach ($this->_relations as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				switch ($type)
				{
					case 'value':
						$value = $value_from;
						break;
					case 'property':
						$value = $obj->{$value_from};
						break;
					default :
						throw new \FuelException('Orm observer setting error.');
						break;
				}
				$query = $query->where($property_to, $value);
			}
		}
		$models = $query->get();
		foreach ($models as $model)
		{
			$model->{$this->_property_to} = $obj->{$this->_property_from};
			$model->save();
		}
	}
}
// End of file updatetimelinedatetime.php
