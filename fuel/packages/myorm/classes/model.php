<?php
namespace MyOrm;

class Model extends \Orm\Model
{
	public static function check_authority($id, $target_member_id = 0, $related_tables = array(), $member_id_prop = 'member_id')
	{
		if (!$id) throw new \HttpNotFoundException;

		$params = array('rows_limit' => 1);
		if ($related_tables && !is_array($related_tables)) $related_tables = (array)$related_tables;
		if ($related_tables) $params['related'] = $related_tables;
		if (!$obj = self::find($id, $params)) throw new \HttpNotFoundException;
		if ($target_member_id && $obj->{$member_id_prop} != $target_member_id) throw new \HttpForbiddenException;

		return $obj;
	}

	public static function get_row4unique_key(array $conditions)
	{
		return self::query()
			->where($conditions)
			->get_one();
	}

	public static function get_list($params = array(), $limit = 0, $is_latest = false, $is_desc = false, $since_id = 0, $max_id = 0, $relateds = array(), $is_return_array = false, $is_return_all_count = false, $select_props = array(), $sort_prop = 'id')
	{
		$is_reverse = false;
		if ($limit && $is_latest && !$is_desc)
		{
			$is_desc = true;
			$is_reverse = true;
		}

		$query = self::query();
		if (!is_array($params)) $params = (array)$params;
		if ($params) $query->where($params);
		$all_records_count = $is_return_all_count ? $query->count() : false;

		$params = array();
		if ($since_id)
		{
			$params[] = array($sort_prop, '>', $since_id);
		}
		if ($max_id)
		{
			$params[] = array($sort_prop, '<=', $max_id);
		}
		if ($params) $query->where($params);

		if ($select_props)
		{
			if (!is_array($select_props)) $select_props = (array)$select_props;
			foreach ($select_props as $select_prop) $query->select($select_prop);
		}

		if ($relateds) $query->related($relateds);

		if ($limit)
		{
			$rows_limit = $limit + 1;
			$query->rows_limit($rows_limit);
		}
		$query->order_by($sort_prop, ($is_desc) ? 'desc' : 'asc');
		$list = $query->get();

		$next_id = 0;
		if ($limit && count($list) > $limit)
		{
			$next_obj = array_pop($list);
			$next_id = $next_obj->id;
		}
		if ($is_reverse) $list = array_reverse($list);

		if ($is_return_array)
		{
			$list_array = array();
			foreach ($list as $key => $obj) $list_array[] = $obj->to_array();
		}

		return array($is_return_array ? $list_array : $list, $next_id, $all_records_count);
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

	public static function get_cols($col, $params)
	{
		$query = self::query()->select($col);
		if ($params) $query->where($params);

		return \Util_Orm::conv_col2array($query->get(), $col);
	}
}
