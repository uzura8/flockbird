<?php
namespace Model;

class Bplace extends \Model
{
	public static function get_result_array_all($cols = array())
	{
		return \DB::select_array($cols)
			->from('b_place')
			->order_by('place_id')
			->execute()
			->as_array();
	}
}
