<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return array(

	/**
	 * DB connection, leave null to use default
	 */
	'db_connection' => null,

	/**
	 * DB table name for the user table
	 */
	'table_name' => 'admin_user',

	/**
	 * Choose which columns are selected, must include: username, password, email, last_login,
	 * login_hash, group & profile_fields
	 */
	'table_columns' => array('*'),

	/**
	 * This will allow you to use the group & acl driver for non-logged in users
	 */
	'guest_login' => false,

	/**
	 * Groups as id => array(name => <string>, roles => <array>)
	 */
	'groups' => array(
		/**
		 * Examples
		 * ---
		 *
		 * -1   => array('name' => 'Banned', 'roles' => array('banned')),
		 * 0    => array('name' => 'Guests', 'roles' => array()),
		 * 1    => array('name' => 'Users', 'roles' => array('user')),
		 * 50   => array('name' => 'Moderators', 'roles' => array('user', 'moderator')),
		 * 100  => array('name' => 'Administrators', 'roles' => array('user', 'moderator', 'admin')),
		 */
		//-1   => array('name' => 'Banned', 'roles' => array('banned')),
		//0    => array('name' => 'Guests', 'roles' => array()),
		1    => array('name' => 'Users', 'roles' => array('user')),
		50   => array('name' => 'Moderators', 'roles' => array('user', 'moderator')),
		100  => array('name' => 'Administrators', 'roles' => array('user', 'moderator', 'admin')),
	),

	/**
	 * Roles as name => array(location => rights)
	 */
	'roles' => array(
		'admin'  => true,
		'moderator'  => array(
			'admin_admin_index' => array('GET'),
			'admin_admin_logout' => array('GET', 'POST'),
			'admin_account_index' => array('GET'),
			'admin_setting_index' => array('GET'),
			'admin_setting_password' => array('GET', 'POST'),
			'admin_setting_change_password' => array('POST'),
			'admin_setting_email' => array('GET', 'POST'),
			'admin_setting_change_email' => array('POST'),
			'admin_news_index' => array('GET'),
			'admin_news_list' => array('GET'),
			'admin_news_detail' => array('GET'),
			'admin_news_create' => array('GET', 'POST'),
			'admin_news_edit' => array('GET', 'POST'),
			'admin_news_publish' => array('POST'),
			'admin_news_unpublish' => array('POST'),
			'admin_news_category_index' => array('GET'),
			'admin_news_category_edit_all' => array('GET', 'POST'),
			//news_category_api
			//news_image_api
			//filetmp_api
		),
		'user' => array(
			'admin_admin_index' => array('GET'),
			'admin_admin_logout' => array('GET', 'POST'),
			'admin_news_index' => array('GET'),
			'admin_news_list' => array('GET'),
			'admin_news_detail' => array('GET'),
		),
		/**
		 * Examples
		 * ---
		 *
		 * Regular example with role "user" given create & read rights on "comments":
		 *   'user'  => array('comments' => array('create', 'read')),
		 * And similar additional rights for moderators:
		 *   'moderator'  => array('comments' => array('update', 'delete')),
		 *
		 * Wildcard # role (auto assigned to all groups):
		 *   '#'  => array('website' => array('read'))
		 *
		 * Global disallow by assigning false to a role:
		 *   'banned' => false,
		 *
		 * Global allow by assigning true to a role (use with care!):
		 *   'super' => true,
		 */
	),

	/**
	 * Salt for the login hash
	 */
	'login_hash_salt' => PRJ_ENCRYPTION_KEY,

	/**
	 * $_POST key for login username
	 */
	'username_post_key' => 'username',

	/**
	 * $_POST key for login password
	 */
	'password_post_key' => 'password',
);
