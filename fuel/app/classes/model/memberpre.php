<?php
class Model_MemberPre extends \Orm\Model
{
	protected static $_table_name = 'member_pre';
	protected static $_properties = array(
		'id',
		'name',
		'email',
		'password',
		'token' => array(
			'data_type' => 'varchar',
			'form' => array('type' => 'hidden'),
			'validation' => array('trim', 'max_length' => array(255)),
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
		static::$_properties['name'] = Model_Member::property('name');
		static::$_properties['email'] = Model_MemberAuth::property('email');
		static::$_properties['password'] = Model_MemberAuth::property('password');
	}

	public static function get4token($token)
	{
		return self::query()->where('token', $token)->get_one();
	}
}
