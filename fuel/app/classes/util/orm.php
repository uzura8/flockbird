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
}
