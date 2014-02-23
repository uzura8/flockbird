<?php
class Model_MemberPre extends \Orm\Model
{
	protected static $_table_name = 'member_pre';
	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'email' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'password' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'token' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'created_at',
		'updated_at'
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

	public static function get4token($token)
	{
		return self::query()->where('token', $token)->get_one();
	}
}
