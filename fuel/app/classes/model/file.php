<?php
class Model_File extends \MyOrm\Model
{
	protected static $_table_name = 'file';

	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array('trim', 'required', 'max_length' => array(64)),
		),
		'type' => array(
			'validation' => array('trim', 'max_length' => array(256)),
		),
		'filesize' => array(
			'data_type' => 'integer',
			'validation' => array('trim', 'required', 'valid_string' => array('numeric')),
		),
		'original_filename' => array(
			'validation' => array('trim', 'max_length' => array(255)),
		),
		'member_id' => array(
			'data_type' => 'integer',
			'validation' => array('valid_string' => array('numeric')),
		),
		'exif' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
		),
		'shot_at',
		'created_at',
		'updated_at'
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
		'MyOrm\Observer_AddMemberFilesizeTotal' => array(
			'events' => array('before_insert'),
			'key_from' => 'member_id',
			'key_to' => 'id',
			'property_from' => 'filesize',
		),
		'MyOrm\Observer_SubtractMemberFilesizeTotal' => array(
			'events' => array('after_delete'),
			'key_from' => 'member_id',
			'key_to' => 'id',
			'property_from' => 'filesize',
		),
	);

	protected static $_to_array_exclude = array('exif');

	public static function _init()
	{
		if (conf('upload.isRemoveOnBatch'))
		{
			static::$_observers['MyOrm\Observer_InsertFileBinDeleteQueue'] = array(
				'events' => array('before_delete'),
			);
		}
		else
		{
			static::$_observers['MyOrm\Observer_RemoveFile'] = array(
				'events' => array('before_delete'),
			);
		}
	}

	public static function calc_filesize_total($member_id = 0)
	{
		if (!$member_id) throw new InvalidArgumentException('First parameter is invalid.');

		$result = DB::query('SELECT SUM(filesize) as sum FROM file WHERE member_id = :member_id')->param('member_id', $member_id)->execute();
		if (!array_key_exists('sum', $result[0])) throw new FuelException('SQL result error.');

		return (int)$result[0]['sum'];
	}

	public static function get4name($name)
	{
		return self::query()->where('name', $name)->get_one();
	}

	public static function get4names($names)
	{
		return self::query()->where('name', 'in', $names)->get();
	}

	public static function get_id4name($name)
	{
		if (!$obj = self::get4name($name)) return false;

		return $obj->id;
	}

	public static function check_name_exists($name)
	{
		return (bool)self::query()->where('name', $name)->get_one();
	}

	public static function delete_with_timeline($name)
	{
		if (!$obj = self::get4name($name)) return false;

		if (is_enabled('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('file', $obj->id);
		$deleted_filesize = $obj->filesize;
		$obj->delete();

		return $deleted_filesize;
	}

	public static function move_from_file_tmp(Model_FileTmp $file_tmp, $new_filename_prefix = '', $is_ignore_member_id = false)
	{
		$file = static::forge();
		$file->name = $file_tmp->name;
		if ($new_filename_prefix)
		{
			$file->name = Site_Upload::change_filename_prefix($file_tmp->name, $new_filename_prefix);
		}
		$file->filesize = $file_tmp->filesize;
		$file->original_filename = $file_tmp->original_filename;
		$file->type = $file_tmp->type;
		if (!$is_ignore_member_id) $file->member_id = $file_tmp->member_id;
		if (!is_null($file_tmp->exif)) $file->exif = $file_tmp->exif;
		if (!empty($file_tmp->shot_at)) $file->shot_at = $file_tmp->shot_at;
		$file->save();

		if (conf('upload.isSaveDb'))
		{
			$file_bin = Model_FileBin::get4name($file_tmp->name);
			$file_bin->name = $file->name;
			$file_bin->save();
		}

		$file_tmp->delete();

		return $file;
	}
}
