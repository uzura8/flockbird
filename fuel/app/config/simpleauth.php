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
			'admin/news/create' => array('GET', 'POST'),
			'admin/news/delete' => array('POST', 'DELETE'),
			'admin/news/edit' => array('GET', 'POST'),
			'admin/news/publish' => array('POST'),
			'admin/news/unpublish' => array('POST'),
			'admin/news/image/api/delete' => array('POST', 'DELETE'),
			'admin/news/file/api/delete' => array('POST', 'DELETE'),
			'admin/news/category/index' => array('GET'),
			'admin/news/category/edit_all' => array('GET', 'POST'),
			'admin/news/category/api/create' => array('POST'),
			'admin/news/category/api/delete' => array('POST'),
			'admin/news/category/api/update' => array('POST'),
			'admin/news/category/image/delete' => array('POST'),
			'admin/content/page/create' => array('GET', 'POST'),
			'admin/content/page/delete' => array('POST', 'DELETE'),
			'admin/content/page/edit' => array('GET', 'POST'),
			'admin/content/image/upload' => array('GET', 'POST'),
			'admin/content/image/delete' => array('POST'),
			'admin/content/template/mail/edit' => array('GET', 'POST'),
			'admin/content/template/mail/reset' => array('POST'),
			'admin/content/image/api/delete' => array('POST'),
			'admin/filetmp/api/upload' => array('GET', 'POST', 'DELETE'),
			'admin/account/index' => array('GET'),
		),
		'user' => array(
			'admin/index' => array('GET'),
			'admin/logout' => array('GET', 'POST'),
			'admin/setting/index' => array('GET'),
			'admin/setting/password' => array('GET', 'POST'),
			'admin/setting/change_password' => array('POST'),
			'admin/setting/email' => array('GET', 'POST'),
			'admin/setting/change_email' => array('POST'),
			'admin/news/index' => array('GET'),
			'admin/news/list' => array('GET'),
			'admin/news/detail' => array('GET'),
			'admin/content/index' => array('GET'),
			'admin/content/page/index' => array('GET'),
			'admin/content/page/list' => array('GET'),
			'admin/content/image/index' => array('GET'),
			'admin/content/image/list' => array('GET'),
			'admin/content/image/detail' => array('GET'),
			'admin/content/image/api/list' => array('GET'),
			'admin/content/template/mail/index' => array('GET'),
			'admin/content/template/mail/list' => array('GET'),
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
	'login_hash_salt' => FBD_ENCRYPTION_KEY,

	/**
	 * $_POST key for login username
	 */
	'username_post_key' => 'username',

	/**
	 * $_POST key for login password
	 */
	'password_post_key' => 'password',
);
