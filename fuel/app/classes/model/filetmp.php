<?php
class Model_FileTmp extends \Orm\Model
{
	protected static $_table_name = 'file_tmp';
	protected static $_has_many = array(
		'file_tmp_config' => array(
			'key_from' => 'id',
			'model_to' => 'Model_FileTmpConfig',
			'key_to' => 'file_tmp_id',
		)
	);
	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
		'path' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
		'type' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
		'filesize' => array(
			'validation' => array('trim', 'valid_string' => array('integer')),
		),
		'original_filename' => array(
			'validation' => array('trim', 'max_length' => array(255)),
		),
		'member_id' => array(
			'validation' => array('trim', 'valid_string' => array('integer')),
		),
		'description' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'cols' => 60, 'rows' => 2, 'placeholder' => '写真の説明', 'class' => 'col-xs-12'),
		),
		'exif' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
		),
		'type' => array(
			'validation' => array('trim', 'max_length' => array(64)),
		),
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
		'MyOrm\Observer_RemoveFile' => array(
			'events' => array('before_delete'),
			'is_tmp' => true,
		),
	);

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id);
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}

	public static function get4name_and_member_id($name, $member_id)
	{
		return self::query()
			->where('name', $name)
			->where('member_id', $member_id)
			->get_one();
	}

	public static function get4name($name)
	{
		return self::query()->where('name', $name)->get_one();
	}
}
