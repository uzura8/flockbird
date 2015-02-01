<?php

class Model_MemberDeleteQueue extends \MyOrm\Model
{
	protected static $_table_name = 'member_delete_queue';

	protected static $_properties = array(
		'id',
		'member_id',
		'name',
		'email',
		'created_at' => array('form' => array('type' => false)),
	);

	public static function _init()
	{
		static::$_properties['member_id'] = Util_Orm::get_relational_numeric_key_prop();
		static::$_properties['name'] = Model_Member::property('name');
		static::$_properties['email'] = Model_MemberAuth::property('email');
	}

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
	);
}
