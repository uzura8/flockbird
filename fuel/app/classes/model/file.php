<?php
class Model_File extends \Orm\Model
{
	protected static $_table_name = 'file';
	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array(
				'trim',
				'max_length' => array(64),
			),
		),
		'path' => array(
			'validation' => array(
				'trim',
				'max_length' => array(64),
			),
		),
		'type' => array(
			'validation' => array(
				'trim',
				'max_length' => array(64),
			),
		),
		'filesize' => array(
			'validation' => array(
				'trim',
				'valid_string' => array('integer'),
			),
		),
		'original_filename' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'member_id' => array(
			'validation' => array(
				'trim',
				'valid_string' => array('integer'),
			),
		),
		'exif',
		'shot_at',
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
		'MyOrm\Observer_AddMemberFilesizeTotal' => array(
			'events' => array('before_insert'),
			'key_from' => 'member_id',
			'model_to' => '\Model_Member',
			'key_to' => 'id',
			'property_from' => 'filesize',
			'property_to' => 'filesize_total',
		),
		'MyOrm\Observer_SubtractMemberFilesizeTotal' => array(
			'events' => array('after_delete'),
			'key_from' => 'member_id',
			'model_to' => '\Model_Member',
			'key_to' => 'id',
			'property_from' => 'filesize',
			'property_to' => 'filesize_total',
		),
		'MyOrm\Observer_RemoveFile' => array(
			'events' => array('after_delete'),
		),
	);

	protected static $name_list = array();

	public static function validate($factory)
	{
		$val = Validation::forge($factory);
		//$val->add_field('title', 'Title', 'required|max_length[255]');

		return $val;
	}

	public static function calc_filesize_total($member_id = 0)
	{
		if (!$member_id) return false;

		$result = DB::query('SELECT SUM(filesize) as sum FROM file WHERE member_id = :member_id')->param('member_id', $member_id)->execute();

		return (int)$result[0]['sum'];
	}

	public static function get_name($id)
	{
		if (!empty(self::$name_list[$id])) return self::$name_list[$id];

		self::$name_list[$id] = '';
		if ($file = self::query()->select('name', 'path')->where('id', $id)->get_one())
		{
			self::$name_list[$id] = array('name' => $file->name, 'path' => $file->path);
		}

		return self::$name_list[$id];
	}

	public static function save_from_file_tmp($file_tmp)
	{
		$obj = new self;
		$ignore_columns = array(
			'id',
			'contents',
			'hash',
			'created_at',
			'updated_at'
		);
		foreach (self::$_properties as $key => $value)
		{
			$column = (is_string($key) && is_array($value)) ? $key : $value;

			if (in_array($column, $ignore_columns)) continue;
			$obj->$column = $file_tmp->$column;
		}
		$obj->save();

		return $obj;
	}

	public static function delete_with_file($id)
	{
		if (!$self = self::find($id)) return false;

		\Timeline\Site_Model::delete_timeline('file', $id);
		Site_Upload::remove_images($self->path, $self->name);

		$deleted_filesize = $self->filesize;
		$self->delete();

		return $deleted_filesize;
	}

	public static function move_from_file_tmp(Model_FileTmp $file_tmp, $new_filepath)
	{
		$file = new self;
		$file->name = $file_tmp->name;
		$file->path = $new_filepath;
		$file->filesize = $file_tmp->filesize;
		$file->original_filename = $file_tmp->original_filename;
		$file->type = $file_tmp->type;
		$file->member_id = $file_tmp->member_id;
		if (!is_null($file_tmp->exif)) $file->exif = $file_tmp->exif;
		if (!empty($file_tmp->shot_at)) $file->shot_at = $file_tmp->shot_at;
		$file->save();
		$file_tmp->delete();

		return $file;
	}
}
