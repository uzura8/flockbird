<?php
namespace Album;

class Model_AlbumImageLike extends \MyOrm\Model
{
	protected static $_table_name = 'album_image_like';

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
		'album_image_id' => array(
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
					'model_to' => '\Album\Model_AlbumImage',
					'conditions' => array(
						'id' => array(
							'album_image_id' => 'property',
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
					'model_to' => '\Album\Model_AlbumImage',
					'conditions' => array(
						'id' => array(
							'album_image_id' => 'property',
						),
					),
					'update_property' => 'like_count',
				),
			),
		),
	);

	protected static $count_per_album_image = array();

	public static function _init()
	{
		if (\Module::loaded('timeline'))
		{
			static::$_observers['MyOrm\Observer_InsertMemberFollowTimeline'] = array(
				'events'   => array('after_insert'),
				'timeline_relations' => array(
					'foreign_table' => array(
						'album_image' => 'value',
					),
					'foreign_id' => array(
						'album_image_id' => 'property',
					),
				),
				'property_from_member_id' => 'member_id',
			);
		}
		if (is_enabled('notice'))
		{
			static::$_observers['MyOrm\Observer_InsertNotice'] = array(
				'events'   => array('after_insert'),
				'update_properties' => array(
					'foreign_table' => array('album_image' => 'value'),
					'foreign_id' => array('album_image_id' => 'property'),
					'type_key' => array('like' => 'value'),
					'member_id_from' => array('member_id' => 'property'),
					'member_id_to' => array(
						'related' => array(
							'album_image' => array('album' => 'member_id'),
						),
					),
				),
			);
			$type = \Notice\Site_Util::get_notice_type('like');
			static::$_observers['MyOrm\Observer_DeleteNotice'] = array(
				'events' => array('before_delete'),
				'conditions' => array(
					'foreign_table' => array('album_image' => 'value'),
					'foreign_id' => array('album_image_id' => 'property'),
					'type' => array($type => 'value'),
				),
			);
		}
	}

	public static function get_count4album_image_id($album_image_id)
	{
		if (!empty(self::$count_per_album_image[$album_image_id])) return self::$count_per_album_image[$album_image_id];

		$query = self::query()->select('id')->where('album_image_id', $album_image_id);
		self::$count_per_album_image[$album_image_id] = $query->count();

		return self::$count_per_album_image[$album_image_id];
	}

	public static function check_liked($album_image_id, $member_id)
	{
		return (bool)$query = self::query()
			->where('album_image_id', $album_image_id)
			->where('member_id', $member_id)
			->get_one();
	}
}
