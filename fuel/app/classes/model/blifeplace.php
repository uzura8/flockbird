<?php
namespace Model;

class Blifeplace extends \Model
{
	public static function get_result_array_all($cols = array())
	{
		return \DB::select_array($cols)
			->from('b_life_place')
			->order_by('id')
			->execute()
			->as_array();
	}
}
