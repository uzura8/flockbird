<?php
namespace Timeline;

class Model_TimelineComment extends \Orm\Model
{
	protected static $_table_name = 'timeline_comment';

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
		'MyOrm\Observer_UpdateRelationalTable'=>array(
			'events'=>array('after_insert'),
			'model_to' => '\Timeline\Model_Timeline',
			'relations' => array(
				'id' => array(
					'timeline_id' => 'property',
				),
			),
			'property_from' => 'created_at',
			'property_to' => 'sort_datetime',
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
		'MyOrm\Observer_CountUpToRelations'=>array(
			'events'   => array('after_insert'),
			'relations' => array(
				array(
					'model_to' => '\Timeline\Model_TimelineCache',
					'property' => 'comment_count',
					'conditions' => array(
						'timeline_id' => array(
							'timeline_id' => 'property',
						),
					),
				),
			),
		),
		'MyOrm\Observer_CountDownToRelations'=>array(
			'events'   => array('after_delete'),
			'relations' => array(
				array(
					'model_to' => '\Timeline\Model_TimelineCache',
					'property' => 'comment_count',
					'conditions' => array(
						'timeline_id' => array(
							'timeline_id' => 'property',
						),
					),
				),
			),
		),
	);

	protected static $count_per_timeline = array();

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);
		$val->add_field('body', 'コメント', 'required');

		return $val;
	}

	public static function check_authority($id, $target_member_id = 0, $accept_member_ids = array())
	{
		if (!$id) return false;

		$obj = self::find($id, array('rows_limit' => 1, 'related' => array('timeline', 'member')))? : null;
		if (!$obj) return false;

		$accept_member_ids[] = $obj->member_id;
		$accept_member_ids[] = $obj->timeline->member_id;
		if ($target_member_id && !in_array($target_member_id, $accept_member_ids)) return false;

		return $obj;
	}

	public static function get_count4timeline_id($timeline_id)
	{
		if (!empty(self::$count_per_timeline[$timeline_id])) return self::$count_per_timeline[$timeline_id];

		$query = self::query()->select('id')->where('timeline_id', $timeline_id);
		self::$count_per_timeline[$timeline_id] = $query->count();

		return self::$count_per_timeline[$timeline_id];
	}

	public static function get_comments($timeline_id, $record_limit = 0, $params = array(), $is_desc = false)
	{
		$is_all_records = false;
		$params = array_merge(array(array('timeline_id', '=', $timeline_id)), $params);
		$query = self::query()->where($params);
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
