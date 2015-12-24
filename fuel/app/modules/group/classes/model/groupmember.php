<?php
namespace Group;

class Model_GroupMember extends \MyOrm\Model
{
	protected static $_table_name = 'group_member';

	protected static $_belongs_to = array(
		'group' => array(
			'key_from' => 'group_id',
			'model_to' => '\Group\Model_Group',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);
	protected static $_has_one = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'group_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'role_type' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(2)),
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

	protected static $_to_array_exclude = array();

	public static function _init()
	{
		static::$_properties['role_type']['validation']['in_array'][] = array_values(\Config::get('group.member.types'));
	}

	public static function get_member_ids4group_id($group_id)
	{
		return (array)self::get_cols('member_id', array('group_id' => $group_id), 'id');
	}

	public static function get_members($group_id)
	{
		$members = array();
		if (!$objs = self::get_all('id', array('member'), 0, array('group_id' => $group_id))) return $members;
		foreach ($objs as $obj)
		{
			$members[$obj->member_id] = $obj->member;
		}

		return $members;
	}
}
