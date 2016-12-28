<?php
class Model_MemberEmailPre extends \MyOrm\Model
{
	protected static $_table_name = 'member_email_pre';
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
		'email' => array('form' => array('type' => false)),
		'token' => array('form' => array('type' => false)),
		'code' => array(
			'data_type' => 'varchar',
			'label' => '確認コード',
			'validation' => array(
				'trim',
				'required',
				'valid_string' => array('numeric'),
				'exact_length' => array(6),
			),
			'form' => array('type' => 'text', 'class' => 'input-xlarge form-control', 'pattern' => '\d*'),
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
		static::$_properties['email'] = Model_MemberAuth::property('email');
		static::$_properties['token'] = Model_MemberPre::property('token');
		static::$_properties['token']['form']['type'] = false;
		static::$_properties['code']['label'] = t('site.confirmation_code');
		static::$_properties['code']['form']['validation']['exact_length'] = array(conf('member.setting.email.codeLength'));
	}

	public static function get4member_id($member_id)
	{
		return self::query()->where('member_id', $member_id)->get_one();
	}

	public static function get4token($token)
	{
		return self::query()->where('token', $token)->get_one();
	}

	public static function save_with_token($member_id, $email)
	{
		if (!$obj = Model_MemberEmailPre::get4member_id($member_id)) $obj = Model_MemberEmailPre::forge();

		$obj->member_id = $member_id;
		$obj->email     = $email;
		$obj->token     = Security::generate_token();
		$obj->code      = Util_String::get_random_code(static::$_properties['code']['form']['validation']['exact_length'][0]);
		if (!$obj->save()) return false;

		return $obj;
	}
}
