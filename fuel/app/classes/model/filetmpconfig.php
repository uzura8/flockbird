<?php
class Model_FileTmpConfig extends \MyOrm\Model
{
	protected static $_table_name = 'file_tmp_config';

	protected static $_belongs_to = array(
		'file_tmp' => array(
			'key_from' => 'file_tmp_id',
			'model_to' => 'Model_FileTmp',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'file_tmp_id',
		'name',
		'value',
		'created_at',
		'updated_at',
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

	public static function get_for_name($file_tmp_id, $name)
	{
		$obj = self::query()
			->where('file_tmp_id', $file_tmp_id)
			->where('name', $name)
			->order_by('id')
			->get_one();

		if (!$obj) return null;

		return $obj;
	}

	public static function get_value_for_name($file_tmp_id, $name)
	{
		if (!$obj = self::get_for_name($file_tmp_id, $name)) return '';

		return $obj->value;
	}

	public static function update_for_name($file_tmp_id, $name, $value)
	{
		if (!$obj = self::get_for_name($file_tmp_id, $name))
		{
			if (!strlen($value)) return null;

			$obj = new self;
			$obj->file_tmp_id = $file_tmp_id;
			$obj->name = $name;
		}
		$obj->value = $value;
		$obj->save();

		return $obj;
	}
}
