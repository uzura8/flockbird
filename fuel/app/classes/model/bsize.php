<?php
namespace Model;

class Bsize extends \Model
{
	public static function get_result_array_all($cols = array())
	{
		return \DB::select_array($cols)
			->from('b_size')
			->order_by('id')
			->execute()
			->as_array();
	}
}
