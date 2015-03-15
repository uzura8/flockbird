<?php
namespace Thread;

class Model_ThreadComment extends \MyOrm\Model
{
	protected static $_table_name = 'thread_comment';

	protected static $_belongs_to = array(
		'thread' => array(
			'key_from' => 'thread_id',
			'model_to' => '\Thread\Model_Thread',
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
		'thread_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'body',
		'like_count' => array(
			'data_type' => 'integer',
			'default' => 0,
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
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
		'MyOrm\Observer_CountUpToRelations'=>array(
			'events'   => array('after_insert'),
			'relations' => array(
				array(
					'model_to' => '\Thread\Model_Thread',
					'conditions' => array(
						'id' => array('thread_id' => 'property'),
					),
					'optional_updates' => array(
						'sort_datetime' => array('created_at' => 'property'),
					),
				),
			),
		),
		'MyOrm\Observer_CountDownToRelations'=>array(
			'events'   => array('after_delete'),
			'relations' => array(
				array(
					'model_to' => '\Thread\Model_Thread',
					'conditions' => array(
						'id' => array('thread_id' => 'property'),
					),
				),
			),
		),
	);

	protected static $count_per_thread = array();

	public static function _init()
	{
		if (\Module::loaded('timeline'))
		{
			static::$_observers['MyOrm\Observer_InsertMemberFollowTimeline'] = array(
				'events'   => array('after_insert'),
				'timeline_relations' => array(
					'foreign_table' => array(
						'thread' => 'value',
					),
					'foreign_id' => array(
						'thread_id' => 'property',
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
					'foreign_table' => array('thread' => 'value'),
					'foreign_id' => array('thread_id' => 'property'),
					'type_key' => array('comment' => 'value'),
					'member_id_from' => array('member_id' => 'property'),
					'member_id_to' => array(
						'related' => array('thread' => 'member_id'),
					),
				),
			);
			$type = \Notice\Site_Util::get_notice_type('comment');
			static::$_observers['MyOrm\Observer_DeleteNotice'] = array(
				'events' => array('before_delete'),
				'conditions' => array(
					'foreign_table' => array('thread' => 'value'),
					'foreign_id' => array('thread_id' => 'property'),
					'type' => array($type => 'value'),
				),
			);
		}
	}

	public static function check_authority($id, $target_member_id = 0, $related_tables = null, $member_id_prop = 'member_id')
	{
		if (is_null($related_tables)) $related_tables = array('thread', 'member');

		$id = (int)$id;
		if (!$id) throw new \HttpNotFoundException;

		$params = array('rows_limit' => 1);
		if ($related_tables) $params['related'] = $related_tables;
		if (!$obj = self::find($id, $params)) throw new \HttpNotFoundException;

		$accept_member_ids = array($obj->{$member_id_prop}, $obj->thread->{$member_id_prop});
		if ($target_member_id && !in_array($target_member_id, $accept_member_ids))
		{
			throw new \HttpForbiddenException;
		}

		return $obj;
	}

	public static function get4thread_id($thread_id)
	{
		return self::query()->where('thread_id', $thread_id)->get();
	}

	public static function get_count4thread_id($thread_id)
	{
		if (!empty(self::$count_per_thread[$thread_id])) return self::$count_per_thread[$thread_id];

		$query = self::query()->select('id')->where('thread_id', $thread_id);
		self::$count_per_thread[$thread_id] = $query->count();

		return self::$count_per_thread[$thread_id];
	}
}
