<?php

class Model_MemberRelation extends \MyOrm\Model
{
	protected static $_table_name = 'member_relation';
	protected static $_has_one = array(
		'member_from' => array(
			'key_from' => 'member_id_from',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => true,
		),
		'member' => array(
			'key_from' => 'member_id_to',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => true,
		),
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

	public static function _init()
	{
		if (is_enabled('notice') && \Notice\Site_Util::check_enabled_notice_type('follow'))
		{
			$type_key = 'follow';
			$type = \Notice\Site_Util::get_notice_type($type_key);
			static::$_observers['MyOrm\Observer_InsertNotice'] = array(
				'events'   => array('after_save'),
				'update_properties' => array(
					'foreign_table' => array('member_relation' => 'value'),
					'foreign_id' => array('id' => 'property'),
					'type_key' => array($type_key => 'value'),
					'member_id_from' => array('member_id_from' => 'property'),
					'member_id_to' => array('member_id_to' => 'property'),
				),
				'check_changed' => array(
					'check_properties' => array(
						'is_'.$type_key => array(
							'value' => true,
						),
					),
				),
			);
			static::$_observers['MyOrm\Observer_DeleteNotice'] = array(
				'events' => array('before_delete', 'after_update'),
				'conditions' => array(
					'foreign_table' => array('member_relation' => 'value'),
					'foreign_id' => array('id' => 'property'),
					'type' => array($type => 'value'),
				),
				'check_changed' => array(
					'check_properties' => array(
						'is_'.$type_key => array(
							'value' => false,
						),
					),
				),
				'member_id_prop' => 'member_id_from',
				'foreign_table_member_id_prop' => 'member_id_from',
			);
		}
		if (\Notice\Site_Util::check_enabled_notice_type('follow') && conf('memberRelation.friend.triggerType') == 'each_follows')
		{
			// Make friends by each follows
			static::$_observers['MyOrm\Observer_UpdateMemberRelationByFollow'] = array(
				'events' => array('before_save'),
			);
		}
	}

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
		$target_relation_cache = self::$$prop;
		if (isset($target_relation_cache[$key])) return (bool)$target_relation_cache[$key];

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

	public static function get_member_ids($member_id, $relation_type = null, $target_col = 'member_id_to')
	{
		$where_col = ($target_col == 'member_id_to') ? 'member_id_from' : 'member_id_to';

		if (substr($relation_type, 0, 3) != 'is_') $relation_type = 'is_'.$relation_type;
		if ($relation_type && !in_array($relation_type, array('is_follow', 'is_friend', 'is_access_block')))
		{
			throw new InvalidArgumentException('Second parameter is invalid.');
		}

		$query = \DB::select($target_col)->from(self::$_table_name)->where($where_col, $member_id);
		if ($relation_type) $query = $query->and_where($relation_type, 1);
		$result = $query->execute()->as_array();

		return \Util_db::conv_col($result);
	}

	public static function get_count4member_id($member_id, $relation_type = null, $member_id_prop = 'member_id_from')
	{
		if (!in_array($member_id_prop, array('member_id_from', 'member_id_to')))
		{
			throw new InvalidArgumentException('Third parameter is invalid.');
		}
		if (substr($relation_type, 0, 3) != 'is_') $relation_type = 'is_'.$relation_type;
		if ($relation_type && !in_array($relation_type, array('is_follow', 'is_friend', 'is_access_block')))
		{
			throw new InvalidArgumentException('Second parameter is invalid.');
		}

		return self::get_count(array(
			array($member_id_prop, $member_id),
			array($relation_type, 1),
		));
	}
}
