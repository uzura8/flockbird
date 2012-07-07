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
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'album_id',
		'image' => array(
			'validation' => array(
				'trim',
				'max_length' => array(100),
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

		$obj = self::find()->where('id', $id)->related('album')->related('member')->get_one();
		if (!$obj) return false;

		$accept_member_ids[] = $obj->member_id;
		$accept_member_ids[] = $obj->album->member_id;
		if ($target_member_id && !in_array($target_member_id, $accept_member_ids)) return false;

		return $obj;
	}
}
