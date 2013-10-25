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
		'member_id' => array(
			'validation' => array(
				'trim',
				'required',
				'valid_string' => array('integer'),
			),
		),
		'name' => array(
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(64),
			),
		),
		'value' => array(
			'validation' => array(
				'required',
			),
		),
		'created_at',
		'updated_at'
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
	);

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
