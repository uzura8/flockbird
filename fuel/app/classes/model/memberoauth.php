<?php
class Model_MemberOauth extends \MyOrm\Model
{
	protected static $_table_name = 'member_oauth';
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
		'member_id' => array('form' => array('type' => false)),
		'oauth_provider_id' => array(
			'data_type' => 'integer',
			'validation' => array('trim'),
			'form' => array('type' => false),
		),
		'uid' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(255)),
			'form' => array('type' => false),
		),
		'token',
		'secret',
		'expires' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'service_name' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => false),
		),
		'service_url' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(255), 'valid_url'),
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
		static::$_properties['token'] = Model_MemberPre::property('token');
		static::$_properties['token']['form']['type'] = false;
		static::$_properties['secret'] = Model_MemberPre::property('token');
		static::$_properties['secret']['form']['type'] = false;
	}

	/**
	 * Create new member from Oauth response.
	 */
	public function create_member($input = array())
	{
	}
}
