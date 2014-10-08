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
	protected $update_model;

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
			if (isset($props['optional_updates'])) $this->_optional_updates = $props['optional_updates'];
			$this->execute($obj);
		}
	}

	private function execute($obj)
	{
		$models = \Site_Model::get4relation($this->_model_to, $this->_conditions, $obj);
		foreach ($models as $this->update_model)
		{
			//$this->update_model->{$this->_update_property} = \DB::expr(sprintf('`%s` + 1', $this->_update_property));
			$this->update_model->{$this->_update_property} = $this->update_model->{$this->_update_property} + 1;
			$this->set_optional_value($obj);
			$res = $this->update_model->save();
		}
	}

	private function set_optional_value($self_obj)
	{
		if (empty($this->_optional_updates)) return;

		foreach ($this->_optional_updates as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				$value = \Site_Model::get_value_for_observer_setting($self_obj, $value_from, $type);
				$this->update_model->{$property_to} = $value;
			}
		}
	}
}
// End of file countuptorelations.php
