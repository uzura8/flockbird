<?php

class Model_MemberRelation extends \Orm\Model
{
	protected static $_table_name = 'member_relation';
	protected static $_has_one = array(
		'member' => array(
			'key_from' => 'member_id_to',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => true,
		),
		'member' => array(
			'key_from' => 'member_id_from',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => true,
		)
	);

	protected static $_properties = array(
		'id',
		'member_id_to' => array('form' => array('type' => false)),
		'member_id_from' => array('form' => array('type' => false)),
		'is_follow' => array(
			'data_type' => 'integer',
			'validation' => array('in_array' => array(array(0, 1))),
			'form' => array('type' => false),
		),
		'is_friend' => array(
			'data_type' => 'integer',
			'validation' => array('in_array' => array(array(0, 1))),
			'form' => array('type' => false),
		),
		'is_friend_pre' => array(
			'data_type' => 'integer',
			'validation' => array('in_array' => array(array(0, 1))),
			'form' => array('type' => false),
		),
		'is_access_block' => array(
			'data_type' => 'integer',
			'validation' => array('in_array' => array(array(0, 1))),
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
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
	);

	protected static $relations_friend_pre = array();
	protected static $relations_friend = array();
	protected static $relations_follow = array();
	protected static $relations_access_block = array();

	public static function get4member_id_from_to($member_id_from, $member_id_to)
	{
		return self::query()->where('member_id_from', $member_id_from)->where('member_id_to', $member_id_to)->get_one();
	}

	public static function check_relation($relation_type, $self_member_id, $target_member_id)
	{
		if (!in_array($relation_type, array('friend', 'friend_pre', 'follow', 'access_block')))
		{
			throw new InvalidArgumentException('First parameter is invalid.');
		}
		$key  = Util_string::combine_nums(array($self_member_id, $target_member_id), $relation_type == 'friend');
		$prop = 'relations_'.$relation_type;
		if (isset(self::$$prop[$key])) return (bool)self::$$prop[$key];

		self::set_relations_cache4member_id_from_to($self_member_id, $target_member_id);

		return (bool)Arr::get(self::$$prop, $key);
	}

	public static function set_relations_cache4member_id_from_to($member_id_from, $member_id_to)
	{
		$sorted_key = Util_string::combine_nums(array($member_id_from, $member_id_to), true);
		$key = Util_string::combine_nums(array($member_id_from, $member_id_to));
		self::$relations_friend[$sorted_key] = 0;
		self::$relations_friend_pre[$key]    = 0;
		self::$relations_follow[$key]        = 0;
		self::$relations_access_block[$key]  = 0;

		if (!$relation = self::get4member_id_from_to($member_id_from, $member_id_to)) return;
		self::$relations_friend[$sorted_key] = $relation->is_friend;
		self::$relations_friend_pre[$key]    = $relation->is_friend_pre;
		self::$relations_follow[$key]        = $relation->is_follow;
		self::$relations_access_block[$key]  = $relation->is_access_block;

		return;
	}
}
