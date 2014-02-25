<?php
class Model_MemberConfig extends \Orm\Model
{
	protected static $_table_name = 'member_config';
	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
	);
	protected static $_properties = array(
		'id',
		'member_id' => array('form' => array('type' => false)),
		'name' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(64)),
			'form' => array('type' => false),
		),
		'value' => array(
			'data_type' => 'varchar',
			'validation' => array(),
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
		static::$_properties['member_id'] = Util_Orm::get_relational_numeric_key_prop();
	}

	public static function validate($factory)
	{
		$val = Validation::forge($factory);
		return $val;
	}

	public static function get_from_member_id_and_name($member_id, $name)
	{
		return self::query()->where('member_id', $member_id)->where('name', $name)->get_one();
	}

	public static function get_value($member_id, $name)
	{
		$obj = self::get_from_member_id_and_name($member_id, $name);
		if (!$obj) return null;

		return $obj->value;
	}
}
