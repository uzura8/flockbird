<?php

class Model_MemberAuth extends \MyOrm\Model
{
	protected static $_table_name = 'member_auth';

	protected static $_properties = array(
		'id',
		'member_id' => array('form' => array('type' => false)),
		'email' => array(
			'data_type' => 'varchar',
			'label' => 'メールアドレス',
			'validation' => array('trim', 'required', 'max_length' => array(255), 'valid_email', 'unique' => array('member_auth.email')),
			'form' => array('type' => 'email', 'class' => 'form-control'),
		),
		'password' => array(
			'data_type' => 'varchar',
			'label' => 'パスワード',
			'validation' => array(
				'trim',
				'min_length' => array(6),
				'max_length' => array(128),
			),
			'form' => array('type' => 'password', 'class' => 'form-control'),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
	);

	protected static $_to_array_exclude = array(
		'member_id', 'email', 'password', 'id', 'created_at', 'updated_at'
	);

	public static function _init()
	{
		//static::$_properties['member_id'] = Util_Orm::get_relational_numeric_key_prop();
		static::$_properties['member_id'] =  array(
			'form' => array('type' => false),
		);
		static::$_properties['email']['label'] = t('member.email');
		static::$_properties['password']['label'] = t('member.password');
	}

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

	public static function save_email($val, $member_id)
	{
		if (!$val = filter_var($val, FILTER_VALIDATE_EMAIL)) return false;

		$obj = self::query()->where('member_id', $member_id)->get_one();
		if (!$obj) $obj = new self;
		$obj->member_id = $member_id;
		$obj->email = $val;

		return $obj->save();
	}

	public static function get4email($email)
	{
		return self::query()->where('email', $email)->get_one();
	}

	public static function get_one4member_id($member_id)
	{
		return self::query()->where('member_id', $member_id)->get_one();
	}
}
