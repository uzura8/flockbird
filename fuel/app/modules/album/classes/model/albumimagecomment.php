<?php
namespace Album;

class Model_AlbumImageComment extends \MyOrm\Model
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
		'body' => array(
			'data_type' => 'varchar',
			'label' => 'コメント',
			'validation' => array('trim', 'required'),
			'form' => array('type' => 'text', 'class' => 'form-control'),
		),
		'created_at',
		'updated_at',
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
		'MyOrm\Observer_CountUpToRelations' => array(
			'events'   => array('after_insert'),
			'relations' => array(
				array(
					'model_to' => '\Album\Model_AlbumImage',
					'conditions' => array(
						'id' => array(
							'album_image_id' => 'property',
						),
					),
				),
			),
		),
		'MyOrm\Observer_CountDownToRelations' => array(
			'events'   => array('after_delete'),
			'relations' => array(
				array(
					'model_to' => '\Album\Model_AlbumImage',
					'conditions' => array(
						'id' => array(
							'album_image_id' => 'property',
						),
					),
				),
			),
		),
	);

	protected static $count_per_album_image_list = array();

	public static function _init()
	{
		if (\Module::loaded('timeline'))
		{
			// 更新時に timeline の sort_datetime を更新
			$observer_key = \Config::get('timeline.types.album');
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
	}

	public static function check_authority($id, $target_member_id = 0, $related_tables = null)
	{
		if (is_null($related_tables)) $related_tables = array('album_image', 'member');

		if (!$id) throw new \HttpNotFoundException;

		$params = array('rows_limit' => 1);
		if ($related_tables) $params['related'] = $related_tables;
		if (!$obj = self::find($id, $params)) throw new \HttpNotFoundException;

		$accept_member_ids = array($obj->member_id, $obj->album_image->album->member_id);
		if ($target_member_id && !in_array($target_member_id, $accept_member_ids))
		{
			throw new \HttpForbiddenException;
		}

		return $obj;
	}

	public static function get_count4album_image_id($album_image_id)
	{
		if (!empty(self::$count_per_album_image_list[$album_image_id])) return self::$count_per_album_image_list[$album_image_id];

		$query = self::query()->select('id')->where('album_image_id', $album_image_id);
		self::$count_per_album_image_list[$album_image_id] = $query->count();

		return self::$count_per_album_image_list[$album_image_id];
	}

	public static function get_comments($album_image_id, $record_limit = 0, $params = array(), $is_desc = false)
	{
		$params = array_merge(array(array('album_image_id', '=', $album_image_id)), $params);;
		$query = self::query()->where($params);

		$is_all_records = false;
		$all_records_count = $query->count();
		$query->related('member');
		if (!$record_limit || $record_limit >= $all_records_count)
		{
			$is_all_records = true;
			$comments = $query->order_by('id', ($is_desc)? 'desc' : 'asc')->get();
		}
		else
		{
			$comments = $query->order_by('id', 'desc')->rows_limit($record_limit)->get();
			if (!$is_desc) $comments = array_reverse($comments);
		}

		return array($comments, $is_all_records, $all_records_count);
	}
}
