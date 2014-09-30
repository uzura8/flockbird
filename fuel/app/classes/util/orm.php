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
		if ($conditions) $query->where($conditions);
		$query->order_by($sort_col, 'desc')->rows_limit(1);

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
		foreach ($related_table_props as $related_table => $prop)
		{
			if (is_array($prop))
			{
				$value = array_merge($values, self::get_related_table_values_recursive($obj->{$related_table}, $prop));
				continue;
			}

			$values[] = $obj->{$related_table}->{$prop};
		}

		return $values;
	}

	public static function check_is_updated(\Orm\Model $obj, $check_properties = array(), $ignore_properties = array())
	{
		if (empty($check_properties) && empty($ignore_properties))
		{
			return true;
		}

		if (\Util_Orm::check_properties_updated($obj, $check_properties)) return true;
		if (\Util_Orm::check_properties_updated_without_ignores($obj, $ignore_properties)) return true;

		return false;
	}

	public static function check_properties_updated(\Orm\Model $obj, $check_properties)
	{
		if (empty($check_properties)) return false;

		$check_properties = (array)$check_properties;
		foreach ($check_properties as $key => $property)
		{
			if (is_array($property))
			{
				$conditions = $property;
				$property = $key;
				foreach ($conditions as $condition => $value)
				{
					if (!$obj->is_changed($property)) continue;
					if ($condition == 'ignore_property')
					{
						if ($obj->is_changed($value)) continue;

						return true;
					}
					if ($condition == 'ignore_value')
					{
						list($before, $after) = \Util_Orm::get_changed_values($obj, $property);
						if ($value == 'reduced_public_flag_range')
						{
							if (Site_Util::check_is_reduced_public_flag_range($before, $after)) continue;
						}
						elseif ($value == 'reduced_num')
						{
							if (preg_match('/`'.$property.'`\s+\-\s+1/', $after)) continue;
							if (is_numeric($before) && is_numeric($after) && $before > $after) continue;
						}

						return true;
					}
				}
			}
			else
			{
				if ($obj->is_changed($property)) return true;
			}
		}

		return false;
	}

	public static function check_properties_updated_without_ignores(\Orm\Model $obj, $ignore_properties)
	{
		if (empty($ignore_properties)) return false;

		$ignore_properties = (array)$ignore_properties;
		$all_properties = \Util_Db::get_columns('timeline');
		foreach ($all_properties as $property)
		{
			if (in_array($property, $ignore_properties)) continue;
			if ($obj->is_changed($property)) return true;
		}

		return false;
	}

	public static function add_query_where(\Orm\Query $query, $conditions = array())
	{
		if (!$conditions) return $query;

		if (count($conditions) == 3)
		{
			$query->where($conditions[0], $conditions[1], $conditions[2]);
		}
		else
		{
			$query->where($conditions);
		}

		return $query;
	}
}
