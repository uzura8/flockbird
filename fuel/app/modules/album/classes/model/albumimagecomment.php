<?php
namespace Album;

class Model_AlbumImageComment extends \Orm\Model
{
	protected static $_table_name = 'album_image_comment';

	protected static $_belongs_to = array(
		'album_image' => array(
			'key_from' => 'album_image_id',
			'model_to' => '\Album\Model_AlbumImage',
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
		'album_image_id',
		'member_id',
		'body',
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

	protected static $count_par_album_image_list = array();

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);
		$val->add_field('body', 'コメント', 'required');

		return $val;
	}

	public static function check_authority($id, $target_member_id = 0, $accept_member_ids = array())
	{
		if (!$id) return false;

		$obj = self::find()->where('id', $id)->related('album_image')->related('member')->get_one();
		if (!$obj) return false;

		$accept_member_ids[] = $obj->member_id;
		$accept_member_ids[] = $obj->album_image->album->member_id;
		if ($target_member_id && !in_array($target_member_id, $accept_member_ids)) return false;

		return $obj;
	}

	public static function get_count4album_image_id($album_image_id)
	{
		if (!empty(self::$count_par_album_image_list[$album_image_id])) return self::$count_par_album_image_list[$album_image_id];

		$query = self::query()->select('id')->where('album_image_id', $album_image_id);
		self::$count_par_album_image_list[$album_image_id] = $query->count();

		return self::$count_par_album_image_list[$album_image_id];
	}

	public static function get_comments($album_image_id, $record_limit = 0, $params = array(), $is_desc = false)
	{
		$params = array_merge(array(array('album_image_id', '=', $album_image_id)), $params);;

		$query = self::find()->where($params)->related('member');
		if (!$record_limit || $record_limit >=$query->count())
		{
			$comments = $query->order_by('id', ($is_desc)? 'desc' : 'asc')->get();
		}
		else
		{
			$comments = $query->order_by('id', 'desc')->rows_limit($record_limit)->get();
			if (!$is_desc) $comments = array_reverse($comments);
		}

		return $comments;
	}
}
