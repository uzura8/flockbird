<?php
namespace Model;

class Birds extends \Model
{
	public static function get_result_array4syllabary_range($initial, $cols = array())
	{
		if (!$range = \Util_db::get_syllabary_range_array($initial)) return false;

		return \DB::select_array($cols)->from('birds')->where('name', 'between', $range)->order_by('name')->execute()->as_array();
	}
}
