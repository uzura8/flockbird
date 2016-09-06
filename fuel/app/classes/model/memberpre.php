<?php
class Model_MemberPre extends \MyOrm\Model
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
		'invite_member_id' => array('form' => array('type' => false)),
		'group' => array(
			'data_type' => 'integer',
			'default' => 1,
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
		static::$_properties['name'] = Model_Member::get_property('name');
		static::$_properties['email'] = Model_MemberAuth::get_property('email');
		static::$_properties['password'] = Model_MemberAuth::get_property('password', 'required');
		static::$_properties['invite_member_id'] = Model_Member::get_property('invite_member_id');
		static::$_properties['group']['validation']['in_array'][] = array_values (conf('group.options', 'member'));
	}

	public static function get4token($token)
	{
		return self::query()->where('token', $token)->get_one();
	}

	public static function get4invite_member_id($member_id)
	{
		return self::query()->where('invite_member_id', $member_id)->get();
	}

	public static function get_one4invite_member_id_and_email($member_id, $email)
	{
		return self::query()
			->where('invite_member_id', $member_id)
			->where('email', $email)
			->get_one();
	}

	public static function save_with_token($email, $password, $invite_member_id = null, $group = null)
	{
		if (is_null($group)) $group = conf('group.defaultValue', 'member');
		$obj = self::forge();
		$obj->email = $email;
		$obj->password = $password;
		$obj->token = Security::generate_token();
		$obj->invite_member_id = $invite_member_id;
		$obj->group = $group;
		if (!$obj->save()) return false;

		return $obj->token;
	}
}
