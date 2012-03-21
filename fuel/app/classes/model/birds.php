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
		$query->join('b_life_place');
		$query->on('birds.b_life_place_id', '=', 'b_life_place.id');
		$query->join('b_watch_spot');
		$query->on('birds.b_watch_spot_id', '=', 'b_watch_spot.id');
		$query->join('b_size');
		$query->on('birds.b_size_id', '=', 'b_size.id');
		$query->where('birds.url', $url);
		$result = $query->execute()->current();// 1件分

		return isset($result) ? $result : false;
	}

	public static function get_result_array4b_life_place_id($b_life_place_id, $cols = array())
	{
		return \DB::select_array($cols)
			->from('birds')
			->where('b_life_place_id', $b_life_place_id)
			->and_where('del_flag', 0)
			->order_by('name')
			->execute()
			->as_array();
	}

	public static function get_result_array4b_watch_spot_id($b_watch_spot_id, $cols = array())
	{
		return \DB::select_array($cols)
			->from('birds')
			->where('b_watch_spot_id', $b_watch_spot_id)
			->and_where('del_flag', 0)
			->order_by('name')
			->execute()
			->as_array();
	}

	public static function get_result_array4b_size_id($b_size_id, $cols = array())
	{
		return \DB::select_array($cols)
			->from('birds')
			->where('b_size_id', $b_size_id)
			->and_where('del_flag', 0)
			->order_by('name')
			->execute()
			->as_array();
	}
}
