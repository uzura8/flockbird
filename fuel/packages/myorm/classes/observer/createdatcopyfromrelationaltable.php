<?php
namespace MyOrm;

class Observer_CreatedAtCopyFromRelationalTable extends \Orm\Observer
{
	public static $mysql_timestamp = true;
	public static $property = 'created_at';
	public static $copy_property = 'created_at';
	public static $property_created_at = 'created_at';

	protected $_model_from;
	protected $_conditions;
	protected $_copy_property;
	protected $_mysql_timestamp;

	/**
	 * @var  string  property to set the timestamp on
	 */
	protected $_property;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_property           = isset($props['property']) ? $props['property'] : static::$property;
		$this->_copy_property      = isset($props['copy_property']) ? $props['copy_property'] : static::$copy_property;
		$this->_model_from         = (array)$props['model_from'];
		$this->_conditions         = (array)$props['conditions'];
		$this->_mysql_timestamp    = isset($props['mysql_timestamp']) ? $props['mysql_timestamp'] : static::$mysql_timestamp;
	}

	public function before_insert(\Orm\Model $obj)
	{
		$this->main($obj);
	}

	private function main(\Orm\Model $obj)
	{
		if (!$datetime = $this->get_datetime_from_relational_model($obj))
		{
			$datetime = $this->_mysql_timestamp ? \Date::time()->format('mysql') : \Date::time()->get_timestamp();
		}

		$obj->{$this->_property} = $datetime;
	}

	private function get_datetime_from_relational_model($obj)
	{
		if (!$relational_model = $this->get_relational_model($obj, $this->_model_from)) return false;
		if (!class_exists($relational_model)) return false;

		$model_from = get_real_class($relational_model);
		$query = $model_from::query();
		foreach ($this->_conditions as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				$value = \Site_Model::get_value_for_observer_setting($obj, $value_from, $type);
				$query->where($property_to, $value);
			}
		}
		$model = $query->get_one();
		if (empty($model->{$this->_copy_property})) return false;

		return $model->{$this->_copy_property};
	}

	private function get_relational_model($obj, $model_from_info)
	{
		if (!$model_from_info) return false;

		if (is_array($model_from_info))
		{
			foreach ($model_from_info as $value_from => $type)
			{
				if ($type == 'model')
				{
					$model_from = $value_from;
					break;
				}
				elseif ($type == 'timeline_related_table')
				{
					if (empty($obj->{$value_from}))
					{
						return false;
					}
					$namespace = \Timeline\Site_Util::get_namespace4foreign_table($obj->{$value_from});
					$model_from = \Site_Model::get_model_name($obj->{$value_from}, $namespace);
				}
			}
		}
		else
		{
			$model_from = $model_from_info;
		}

		return $model_from;
	}
}
// End of file createdatcopyfromrelationaltable.php
