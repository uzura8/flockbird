<?php
namespace Model;

class Birds extends \Model
{
	public static function get_result_array4syllabary_range($initial, $cols = array())
	{
		if (!$range = \Util_db::get_syllabary_range_array($initial)) return false;

		return \DB::select_array($cols)
			->from('birds')
			->where('name', 'between', $range)
			->and_where('del_flag', 0)
			->order_by('name')
			->execute()
			->as_array();
	}

	public static function get4url($url)
	{
		$query = \DB::select()->from('birds');
		$query->join('b_place');
		$query->on('birds.place_id', '=', 'b_place.place_id');
		$query->join('b_lifespot');
		$query->on('birds.wspot', '=', 'b_lifespot.ls_id');
		$query->join('b_size');
		$query->on('birds.size_class', '=', 'b_size.size_id');
		$query->where('birds.url', $url);
		$result = $query->execute()->current();// 1件分

		return isset($result) ? $result : false;
	}

	public static function get_result_array4life_place($life_place, $cols = array())
	{
		return \DB::select_array($cols)
			->from('birds')
			->where('life_place', $life_place)
			->and_where('del_flag', 0)
			->order_by('name')
			->execute()
			->as_array();
	}
}
