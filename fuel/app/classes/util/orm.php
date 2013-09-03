<?php
class Util_Orm
{
	public static function conv_col2array($column, $objs)
	{
		$return = array();
		foreach ($objs as $obj)
		{
			$return[] = $obj->$column;
		}

		return $return;
	}
}
