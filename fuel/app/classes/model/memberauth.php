<?php
class Model_MemberAuth extends \Orm\Model
{
	protected static $_table_name = 'member_auth';
	protected static $_has_one = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => true,
		)
	);
	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'validation' => array('valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'email' => array(
			'data_type' => 'varchar',
			'label' => 'メールアドレス',
			'validation' => array('trim', 'required', 'max_length' => array(255), 'valid_email'),
			'form' => array('type' => 'email', 'class' => 'input-xlarge form-control'),
		),
		'password' => array(
			'data_type' => 'varchar',
			'label' => 'パスワード',
			'validation' => array(
				'trim',
				'required',
				'min_length' => array(6),
				'max_length' => array(128),
			),
			'form' => array('type' => 'password', 'class' => 'input-xlarge form-control'),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
	);

	public static function _init()
	{
		static::$_properties['email']['label'] = term('member.email');
		static::$_properties['password']['label'] = term('member.password');
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

	public static function validate($factory)
	{
		$val = Validation::forge($factory);
		//$val->add_field('title', 'Title', 'required|max_length[255]');

		return $val;
	}

	public static function save_email($val, $member_id)
	{
		if (!$val = filter_var($val, FILTER_VALIDATE_EMAIL)) return false;

		$obj = self::query()->where('member_id', $member_id)->get_one();
		if (!$obj) $obj = new self;
		$obj->member_id = $member_id;
		$obj->email = $val;

		return $obj->save();
	}
}
