<?php
namespace Album;

class Model_AlbumImage extends \Orm\Model
{
	protected static $_table_name = 'album_image';

	protected static $_belongs_to = array(
		'album' => array(
			'key_from' => 'album_id',
			'model_to' => '\Album\Model_Album',
			'key_to' => 'id',
		),
	);
	protected static $_has_one = array(
		'file' => array(
			'key_from' => 'file_id',
			'model_to' => 'Model_File',
			'key_to' => 'id',
			'cascade_save' => false,
			//'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'album_id',
		'file_id' => array(
			'validation' => array(
				'trim',
				'required',
				'valid_string' => array('integer'),
			),
		),
		'name',
		'shot_at',
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

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);

		return $val;
	}

	public static function check_authority($id, $target_member_id = 0, $accept_member_ids = array())
	{
		if (!$id) return false;

		$obj = self::find()->where('id', $id)->related('album')->get_one();
		if (!$obj) return false;

		$accept_member_ids[] = $obj->album->member_id;
		if ($target_member_id && !in_array($target_member_id, $accept_member_ids)) return false;

		return $obj;
	}

	public function get_image()
	{
		if (empty($this->file_id)) return '';

		return \Model_File::get_name($this->file_id);
	}
}
