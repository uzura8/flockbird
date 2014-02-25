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

	public static function get_relational_numeric_key_prop()
	{
		return array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		);
	}
}
