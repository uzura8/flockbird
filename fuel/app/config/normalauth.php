<?php

return array(

	/**
	 * DB connection, leave null to use default
	 */
	'db_connection' => null,

	/**
	 * Salt for the login hash
	 */
	'login_hash_salt' => PRJ_ENCRYPTION_KEY,

	/**
	 * $_POST key for login username
	 */
	'username_post_key' => 'email',

	/**
	 * $_POST key for login password
	 */
	'password_post_key' => 'password',
);
