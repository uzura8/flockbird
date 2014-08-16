<?php
namespace MyOrm;

class Observer_CountDownToRelations extends \Orm\Observer
{
	protected $_relations;
	protected $_model_to;
	protected $_conditions;
	protected $_update_property;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_relations = (array)$props['relations'];
	}

	public function before_delete(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function after_delete(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	private function main(\Orm\Model $obj)
	{
		if (!$this->_relations) return;
		foreach ($this->_relations as $props)
		{
			$this->_model_to = $props['model_to'];
			$this->_conditions = $props['conditions'];
			$this->_update_property = (!empty($props['update_property'])) ? $props['update_property'] : 'comment_count';
			$this->execute($obj);
		}
	}

	private function execute($obj)
	{
		if (!class_exists($this->_model_to))
		{
			throw new \FuelException('Class not found : '.$this->_model_to);
		}
		$model_to = get_real_class($this->_model_to);
		$query = $model_to::query();
		foreach ($this->_conditions as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				$value = \Site_Model::get_value_for_observer_setting($obj, $value_from, $type);
				$query = $query->where($property_to, $value);
			}
		}
		$models = $query->get();
		foreach ($models as $model)
		{
			//$expr = \DB::expr(sprintf('CASE WHEN `%s` -1 < 0 THEN 0 ELSE `%s` - 1 END', $this->_update_property, $this->_update_property));
			//$model->{$this->_update_property} = $expr;
			$model->{$this->_update_property} = $model->{$this->_update_property} - 1;
			if ($model->{$this->_update_property} < 0)
			{
				$model->{$this->_update_property} = 0;
			}
			$model->save();
		}
	}
}
// End of file countdowntorelations.php
