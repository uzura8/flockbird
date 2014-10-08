<?php
namespace MyOrm;

class Observer_UpdateRelationalTables extends \Orm\Observer
{
	protected $_relations;
	protected $_model_to;
	protected $_conditions;
	protected $_update_properties;
	protected $_check_properties;
	protected $_ignore_properties;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_relations = \Arr::is_assoc($props['relations']) ? array($props['relations']) : $props['relations'];
		if (!empty($props['check_changed']))
		{
			$this->_check_properties = isset($props['check_changed']['check_properties']) ? $props['check_changed']['check_properties'] : array();
			$this->_ignore_properties = isset($props['check_changed']['ignore_properties']) ? $props['check_changed']['ignore_properties'] : array();
		}
	}

	public function before_insert(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	public function after_insert(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	public function before_update(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	public function after_update(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	private function main(\Orm\Model $obj)
	{
		if (!$this->_relations) return;
		if (!\Util_Orm::check_is_updated($obj, $this->_check_properties, $this->_ignore_properties))
		{
			return;
		}

		foreach ($this->_relations as $props)
		{
			$this->_model_to = $props['model_to'];
			$this->_conditions = $props['conditions'];
			$this->_update_properties = $props['update_properties'];
			$this->execute($obj);
		}
	}

	private function execute($obj)
	{
		$models = \Site_Model::get4relation($this->_model_to, $this->_conditions, $obj);
		foreach ($models as $model)
		{
			foreach ($this->_update_properties as $property_to => $froms)
			{
				if (!is_array($froms))
				{
					$model->{$froms} = $obj->{$froms};
				}
				else
				{
					foreach ($froms as $value_from => $type)
					{
						$value = \Site_Model::get_value_for_observer_setting($obj, $value_from, $type);
						$model->{$property_to} = $value;
					}
				}
			}
			if (!$model->is_changed()) continue;
			$model->save();
		}
	}
}
// End of file updaterelationaltables.php
