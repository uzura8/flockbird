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
		$models = \Site_Model::get4relation($this->_model_to, $this->_conditions, $obj);
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
