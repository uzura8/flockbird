<?php
namespace Admin;

class Model_AdminUser extends \MyOrm\Model
{
	protected static $_table_name = 'admin_user';

	protected static $_properties = array(
		'id',
		'username' => array(
			'data_type' => 'varchar',
			'label' => 'ユーザ名',
			'validation' => array(
				'trim', 'required',
				'max_length' => array(255),
				'match_pattern' => array('/^[a-z0-9_]*[a-z]+[a-z0-9_]*$/i'),
				'unique' => array('admin_user.username')
			),
			'form' => array('type' => 'text'),
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
			'form' => array('type' => 'password', 'class' => 'form-control'),
		),
		'group' => array(
			'data_type' => 'integer',
			'label' => 'ユーザグループ',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => 'select'),
		),
		'email' => array(
			'data_type' => 'varchar',
			'label' => 'メールアドレス',
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(255),
				'valid_email',
				'unique' => array('admin_user.email')
			),
			'form' => array('type' => 'email', 'class' => 'form-control'),
		),
		'last_login',
		'login_hash',
		'profile_fields',
		'created_at',
		'updated_at',
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
		static::$_properties['username']['label'] = term('admin.account.username');
		static::$_properties['email']['label'] = term('site.email');
		static::$_properties['password']['label'] = term('site.password');
		static::$_properties['group']['label'] = term('admin.user.groups.view');

		$group_options = self::get_group_options();
		static::$_properties['group']['form']['options'] = $group_options;
		static::$_properties['group']['validation']['in_array'][] = array_keys($group_options);
	}

	public static function get_group_options()
	{
		$groups = \Config::get('simpleauth.groups');
		unset($groups[-1], $groups[0]);

		$accepted_groups = \Config::get('admin.user.acceptedGroup');
		$return = array();
		if (!$groups) return $return;

		foreach ($groups as $key => $group)
		{
			if (!in_array($key, $accepted_groups)) continue;
			$return[$key] = term('admin.user.groups.type.'.$key);
		}

		return $return;
	}
}
