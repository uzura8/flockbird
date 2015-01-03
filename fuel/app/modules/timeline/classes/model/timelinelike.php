<?php
namespace Timeline;

class Model_TimelineLike extends \MyOrm\Model
{
	protected static $_table_name = 'timeline_like';

	protected static $_belongs_to = array(
		'timeline' => array(
			'key_from' => 'timeline_id',
			'model_to' => '\Timeline\Model_Timeline',
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
		'timeline_id' => array(
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
					'model_to' => '\Timeline\Model_Timeline',
					'conditions' => array(
						'id' => array(
							'timeline_id' => 'property',
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
					'model_to' => '\Timeline\Model_Timeline',
					'conditions' => array(
						'id' => array(
							'timeline_id' => 'property',
						),
					),
					'update_property' => 'like_count',
				),
			),
		),
		'MyOrm\Observer_InsertRelationialTable'=>array(
			'events'   => array('after_insert'),
			'model_to' => '\Timeline\Model_MemberFollowTimeline',
			'properties' => array(
				'timeline_id' => 'timeline_id',
				'member_id',
			),
			'is_check_duplicated' => array(
				'conditions' => array(
					'timeline_id' => 'timeline_id',
					'member_id',
				),
			),
		),
	);

	protected static $count_per_timeline = array();

	public static function _init()
	{
		if (is_enabled('notice'))
		{
			static::$_observers['MyOrm\Observer_InsertNotice'] = array(
				'events' => array('after_insert'),
				'update_properties' => array(
					'foreign_table' => array('timeline' => 'value'),
					'foreign_id' => array('timeline_id' => 'property'),
					'type_key' => array('like' => 'value'),
					'member_id_from' => array('member_id' => 'property'),
					'member_id_to' => array(
						'related' => array('timeline' => 'member_id'),
					),
				),
			);
			$type = \Notice\Site_Util::get_notice_type('like');
			static::$_observers['MyOrm\Observer_DeleteNotice'] = array(
				'events' => array('before_delete'),
				'conditions' => array(
					'foreign_table' => array('timeline' => 'value'),
					'foreign_id' => array('timeline_id' => 'property'),
					'type' => array($type => 'value'),
				),
			);
		}
	}

	public static function get_count4timeline_id($timeline_id)
	{
		if (!empty(self::$count_per_timeline[$timeline_id])) return self::$count_per_timeline[$timeline_id];

		$query = self::query()->select('id')->where('timeline_id', $timeline_id);
		self::$count_per_timeline[$timeline_id] = $query->count();

		return self::$count_per_timeline[$timeline_id];
	}
}
