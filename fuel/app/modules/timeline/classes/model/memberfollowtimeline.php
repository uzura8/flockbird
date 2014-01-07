<?php
namespace Timeline;

class Model_MemberFollowTimeline extends \Orm\Model
{
	protected static $_table_name = 'member_follow_timeline';

	protected static $_belongs_to = array(
		'timeline' => array(
			'key_from' => 'timeline_id',
			'model_to' => '\Timeline\Model_Timeline',
			'key_to' => 'id',
		),
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => '\Model_Member',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'timeline_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\\Observer_Validation' => array(
			'events' => array('before_save')
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
	);

	public static function get4timeline_id_and_member_id($timeline_id, $member_id)
	{
		return self::query()->where('timeline_id', $timeline_id)->where('member_id', $member_id)->get_one();
	}
}
