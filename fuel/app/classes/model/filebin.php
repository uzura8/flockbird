<?php

class Model_FileBin extends \MyOrm\Model
{
	protected static $_table_name = 'file_bin';
	protected static $_primary_key = array('name');
	//protected static $_write_connection = 'file_bin_db';
	//protected static $_connection = 'file_bin_db';

	protected static $_properties = array(
		'name' => array(
			'validation' => array('trim', 'required', 'max_length' => array(64)),
		),
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

	public static function _init()
	{
		static::set_connections(conf('db.fileBin.configKey', 'file_bin_db'));
	}

	public static function get_bin4name($name, $is_decode = true)
	{
		if (!$obj = self::find($name)) return false;

		return $is_decode ? base64_decode($obj->bin) : $obj->bin;
	}

	public static function get4name($name, $is_tmp = false)
	{
		return self::find($name);
	}

	public static function save_from_file_path($file_path, $save_name = '', $is_image = true)
	{
		if (!$bin = Util_file::get_encoded_bin_data($file_path, $is_image))
		{
			throw new FuelException('Binary data is invalid.');
		}

		$obj = self::forge();
		$filepath = Util_File::get_filepath_from_path($file_path);
		if (!$save_name) $save_name = Site_Upload::convert_filepath2filename($filepath);
		$obj->name = $save_name;
		$obj->bin = $bin;

		return $obj->save();
	}
}
