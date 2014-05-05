<?php
namespace MyOrm;

class Model extends \Orm\Model
{
	public static function check_authority($id)
	{
		if (!$id) return false;
		if (!$obj = self::find($id)) return false;

		return $obj;
	}
}
