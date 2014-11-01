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

	public static function get_bin4file_name($filename, $is_tmp = false, $is_decode = true)
	{
		if (!$file = self::get_file4name($filename, $is_tmp)) return null;

		return self::get_bin4id($file->file_bin_id, $is_decode);
	}

	public static function get4file_name($filename, $is_tmp = false)
	{
		if (!$file = self::get_file4name($filename, $is_tmp)) return null;

		return self::find($file->file_bin_id);
	}

	protected static function get_file4name($filename, $is_tmp = false)
	{
		$model = $is_tmp ? 'Model_FileTmp' : 'Model_File';

		return $model::get4name($filename);
	}

	public static function save_from_file_path($file_path)
	{
		if (!$bin = Util_file::get_encoded_bin_data($file_path, true))
		{
			throw new FuelException('Binary data is invalid.');
		}

		$obj = self::forge();
		$obj->bin = $bin;
		$obj->save();

		return $obj->id;
	}
}
