<?php
namespace MyOrm;

class Observer_CountUpToRelations extends \Orm\Observer
{
	protected $_relations;
	protected $_model_to;
	protected $_conditions;
	protected $_optional_updates;
	protected $_update_property;
	protected $_model;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_relations = (array)$props['relations'];
	}

	public function before_insert(\Orm\Model $obj)
	{
		$this->main($obj);
	}
	public function after_insert(\Orm\Model $obj)
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
			$this->_optional_updates = $props['optional_updates'];
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
		foreach ($models as $this->model)
		{
			$this->model->{$this->_update_property} = \DB::expr(sprintf('`%s` + 1', $this->_update_property));
			$this->set_value_optional($obj);
			$this->model->save();
		}
	}

	private function set_value_optional($self_obj)
	{
		foreach ($this->_optional_updates as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				$value = \Site_Model::get_value_for_observer_setting($self_obj, $value_from, $type);
				$this->model->{$property_to} = $value;
			}
		}
	}
}
// End of file countuptorelations.php
