<?php
class Model_MemberPasswordPre extends \Orm\Model
{
	protected static $_table_name = 'member_password_pre';
	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		)
	);

	protected static $_properties = array(
		'id',
		'member_id' => array('form' => array('type' => false)),
		'email',
		'token',
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
		static::$_properties['email'] = Util_Orm::get_prop('member_auth', 'email');
		static::$_properties['token'] = Util_Orm::get_prop('member_pre', 'token');
		static::$_properties['token']['form']['type'] = false;
	}

	public static function validate($factory)
	{
		$val = Validation::forge($factory);

		return $val;
	}
}
