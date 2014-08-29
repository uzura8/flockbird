<?php
class Util_Orm
{
	public static function conv_col2array($objs, $column)
	{
		$return = array();
		foreach ($objs as $obj)
		{
			$return[] = $obj->$column;
		}

		return $return;
	}

	public static function conv_cols2assoc($objs, $key_col, $value_col)
	{
		$return = array();
		foreach ($objs as $obj)
		{
			$return[$obj->$key_col] = $obj->$value_col;
		}

		return $return;
	}

	public static function get_prop($table, $column, $namespace = '')
	{
		$model = Site_Model::get_model_name($table, $namespace);
		$model_obj = $model::forge();

		return $model_obj::property($column);
	}

	public static function get_relational_numeric_key_prop($is_required = true)
	{
		$field = array(
			'data_type' => 'integer',
			'validation' => array('valid_string' => array('numeric')),
			'form' => array('type' => false),
		);
		if ($is_required) $field['validation'][] = 'required';

		return $field;
	}

	public static function get_changed_values(\Orm\Model $obj, $property = null)
	{
		$values = $obj->get_diff();
		if (!$values) return false;

		if (!$property) return $values;

		return array($values[0][$property], $values[1][$property]);
	}

	public static function get_count_all($model_name, $conditions = array())
	{
		$query = $model_name::query();
		if ($conditions) $query = $query->where($conditions);

		return $query->count();
	}

	public static function get_last_row($model_name, $conditions = array(), $sort_col = 'id')
	{
		$query = $model_name::query();
		if ($conditions) $query = $query->where($conditions);
		$query = $query->order_by($sort_col, 'desc')->rows_limit(1);

		return $query->get_one();
	}

	public static function check_is_changed(\Orm\Model $obj, array $target_properties, array $before_values)
	{
		foreach ($target_properties as $property)
		{
			if ($obj->{$property} != $before_values[$property]) return true;
		}

		return false;
	}

	public static function get_related_table_values_recursive(\Orm\Model$obj, $related_table_props = array())
	{
		$values = array();
		foreach ($related_table_props as $related_table => $values)
		{
			if (is_array($values))
			{
				$value = array_merge($value, self::get_related_table_values_recursive($obj->{$related_table}, $values));
				continue;
			}

			$values[] = $obj->{$related_table}->{$values};
		}

		return $values;
	}
}
