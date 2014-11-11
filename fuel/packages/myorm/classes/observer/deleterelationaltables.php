<?php
namespace MyOrm;

class Observer_DeleteRelationalTables extends \Orm\Observer
{
	protected $_relations;
	protected $_model_to;
	protected $_conditions;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_relations = \Arr::is_assoc($props['relations']) ? array($props['relations']) : $props['relations'];
	}

	public function before_delete(\Orm\Model $obj)
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
			$this->execute($obj);
		}
	}

	private function execute($obj)
	{
		$models = \Site_Model::get4relation($this->_model_to, $this->_conditions, $obj);
		foreach ($models as $model)
		{
			$model->delete();
		}
	}
}
// End of file deleterelationaltables.php
