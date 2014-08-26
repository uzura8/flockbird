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

	public static function get_list($params = array(), $record_limit = 0, $relateds = array(), $is_return_array = false, $is_desc = false, $select_props = array(), $sort_prop = 'id')
	{
		$is_all_records = false;
		$query = self::query();
		if ($select_props)
		{
			if (!is_array($select_props)) $select_props = (array)$select_props;
			foreach ($select_props as $select_prop) $query->select($select_prop);
		}
		$query->where($params);
		$all_records_count = $query->count();
		if ($relateds) $query->related($relateds);
		if (!$record_limit || $record_limit >= $all_records_count)
		{
			$is_all_records = true;
			$query->order_by($sort_prop, ($is_desc)? 'desc' : 'asc');
		}
		else
		{
			$list = $query->order_by('id', 'desc')->rows_limit($record_limit);
		}
		$list = $query->get();
		if (!$is_desc) $list = array_reverse($list);
		if ($is_return_array)
		{
			$list_array = array();
			foreach ($list as $key => $obj) $list_array[] = $obj->to_array();
		}

		return array($is_return_array ? $list_array : $list, $is_all_records, $all_records_count);
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
