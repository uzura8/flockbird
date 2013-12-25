<?php
namespace Timeline;

class Model_Timeline extends \Orm\Model
{
	protected static $_table_name = 'timeline';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
		'member_to' => array(
			'key_from' => 'member_id_to',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		)
	);
//	protected static $_has_many = array(
//		'timeline_child_data' => array(
//			'key_from' => 'id',
//			'model_to' => '\Timeline\Model_TimelineChildData',
//			'key_to' => 'timeline_id',
//		),
//		'timeline_comment' => array(
//			'key_from' => 'id',
//			'model_to' => '\Timeline\Model_TimelineComment',
//			'key_to' => 'timeline_id',
//		),
//		'timeline_cache' => array(
//			'key_from' => 'id',
//			'model_to' => '\Timeline\Model_TimelineCache',
//			'key_to' => 'timeline_id',
//		),
//	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'member_id_to' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'group_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'page_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'type' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(2)),
			'form' => array('type' => false),
		),
		'body' => array(
			'data_type' => 'text',
			'label' => '本文',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'),
		),
		'foreign_table' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(20)),
			'form' => array('type' => false),
		),
		'foreign_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'source' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(64)),
			'form' => array('type' => false),
		),
		'source_uri' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
			'form' => array('type' => false),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'max_length' => array(2)),
			'form' => array(),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
		'sort_datetime' => array('form' => array('type' => false)),
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
		'Orm\\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'MyOrm\Observer_CopyValue'=>array(
			'events'=>array('before_insert'),
			'property_to'   => 'sort_datetime',
			'property_from' => 'created_at',
		),
		'MyOrm\Observer_InsertCache'=>array(
			'events' => array('after_insert'),
			'model_to' => '\Timeline\Model_TimelineCache',
			'properties' => array(
				'timeline_id' => 'id',
				'member_id',
				'member_id_to',
				'group_id',
				'public_flag',
				'created_at',
				'sort_datetime',
			),
		),
		'MyOrm\Observer_InsertCacheDuplicate'=>array(
			'events'   => array('after_insert'),
			'model_to' => '\Timeline\Model_TimelineCache',
			'properties' => array(
				'timeline_id' => 'id',
				'member_id',
				'member_id_to',
				'group_id',
				'public_flag',
				'created_at',
				'sort_datetime',
			),
			'special_properties' => array(
				'is_follow' => array('value' => 1),
			),
		),
		'MyOrm\Observer_UpdateCacheDuplicate'=>array(
			'events'   => array('after_update'),
			'key_from' => 'id',
			'model_to' => '\Timeline\Model_TimelineCache',
			'key_to'   => 'timeline_id',
			'properties' => array(
				'timeline_id' => 'id',
				'member_id',
				'member_id_to',
				'group_id',
				'public_flag',
				'created_at',
				'sort_datetime',
			),
			'special_properties' => array(
				'is_follow' => array('value' => 1),
			),
			'is_check_updated_at' => array(
				'property' => 'sort_datetime',
			),
			'is_update_duplicated' => array(
				'is_insert_new_record' => true,
				'additional_conditions' => array(
					'is_follow' => 1,
				),
			),
		),
		'MyOrm\Observer_InsertRelationialTable'=>array(
			'events'   => array('after_insert'),
			'model_to' => '\Timeline\Model_MemberFollowTimeline',
			'properties' => array(
				'timeline_id' => 'id',
				'member_id',
			),
			'additional_records' => array(
				array(
					'timeline_id' => 'id',
					'member_id' => 'member_id_to',
				),
			),
		),
		'MyOrm\Observer_UpdateRelationalTable'=>array(
			'events'   => array('after_update'),
			'key_from' => 'id',
			'model_to' => '\Timeline\Model_MemberFollowTimeline',
			'table_to' => 'member_follow_timeline',
			'key_to'   => 'timeline_id',
			'property_to'   => 'updated_at',
			'property_from' => 'sort_datetime',
			'is_check_updated_at' => array(
				'property' => 'sort_datetime',
			),
		),
	);

	public static function _init()
	{
		static::$_properties['type']['validation']['in_array'][] = \Config::get('timeline.types');
		static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_accept_timeline_foreign_tables();

		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id);
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}

	public static function get4latest_foreign_data($foreign_table, $foreign_id, $since_datetime = null)
	{
		$query = self::query()
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id);

		if ($since_datetime) $query = $query->where('created_at', '>', $since_datetime);

		return $query->order_by('created_at', 'desc')
			->rows_limit(1)
			->get_one();
	}
}
