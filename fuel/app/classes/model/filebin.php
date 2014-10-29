<?php

class Model_FileBin extends \MyOrm\Model
{
	protected static $_table_name = 'file_bin';

	protected static $_properties = array(
		'id',
		'bin',
		'created_at',
		'updated_at'
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
	);

	public static function get_bin4id($id, $is_decode = true)
	{
		$obj = self::query()->where('id', $id)->get_one();

		return $is_decode ? base64_decode($obj->bin) : $obj->bin;
	}

	public static function get_bin4file_name($filename, $is_decode = true)
	{
		$file = Model_File::get4name($filename);

		return self::get_bin4id($file->file_bin_id, $is_decode);
	}
}
