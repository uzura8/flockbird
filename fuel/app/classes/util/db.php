<?php
class Util_db
{
	public static function get_syllabary_range_array($initial)
	{
		$syllabary_range_list = array(
			'ア' => array('ア%', 'カ%'),
			'カ' => array('カ%', 'サ%'),
			'サ' => array('サ%', 'タ%'),
			'タ' => array('タ%', 'ナ%'),
			'ナ' => array('ナ%', 'ハ%'),
			'ハ' => array('ハ%', 'マ%'),
			'マ' => array('マ%', 'ヤ%'),
			'ヤ' => array('ヤ%', 'ン%'),
		);

		if (empty($syllabary_range_list[$initial])) return false;

		return $syllabary_range_list[$initial];
	}
}
