<?php
namespace Album;

class Model_AlbumImageCommentLike extends \MyOrm\Model
{
	protected static $_table_name = 'album_image_comment_like';

	protected static $_belongs_to = array(
		'album_image_comment' => array(
			'key_from' => 'album_image_comment_id',
			'model_to' => '\Album\Model_AlbumImageComment',
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
		'album_image_comment_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'MyOrm\Observer_CountUpToRelations'=>array(
			'events'   => array('after_insert'),
			'relations' => array(
				array(
					'model_to' => '\Album\Model_AlbumImageComment',
					'conditions' => array(
						'id' => array(
							'album_image_comment_id' => 'property',
						),
					),
					'update_property' => 'like_count',
				),
			),
		),
		'MyOrm\Observer_CountDownToRelations'=>array(
			'events'   => array('after_delete'),
			'relations' => array(
				array(
					'model_to' => '\Album\Model_AlbumImageComment',
					'conditions' => array(
						'id' => array(
							'album_image_comment_id' => 'property',
						),
					),
					'update_property' => 'like_count',
				),
			),
		),
	);

	protected static $count_per_album_image_comment = array();

	public static function get_count4album_image_comment_id($album_image_comment_id)
	{
		if (!empty(self::$count_per_album_image_comment[$album_image_comment_id])) return self::$count_per_album_image_comment[$album_image_comment_id];

		$query = self::query()->select('id')->where('album_image_comment_id', $album_image_comment_id);
		self::$count_per_album_image_comment[$album_image_comment_id] = $query->count();

		return self::$count_per_album_image_comment[$album_image_comment_id];
	}

	public static function check_liked($album_image_comment_id, $member_id)
	{
		return (bool)$query = self::query()
			->where('album_image_comment_id', $album_image_comment_id)
			->where('member_id', $member_id)
			->get_one();
	}
}
