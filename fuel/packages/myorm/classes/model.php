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

	public static function get_row4unique_key(array $conditions)
	{
		return self::query()
			->where($conditions)
			->get_one();
	}

	public static function change_registered_status4unique_key(array $params)
	{
		if ($obj = self::get_row4unique_key($params))
		{
			$obj->delete();
		}
		else
		{
			$obj = self::forge($params);
			$obj->save();
		}

		return $obj->id;
	}
}
