<?php

return array(
	'admin' => array(
		'secure_global_head' => array(
			'Top' => 'admin/',
			'Member' => array(
				'Member list' => 'admin/member',
			),
			'SNS settings' => array(
				'Profile setting' => 'admin/profile',
			),
			'Admin settings' => array(
				'Account management' => 'admin/setting/account',
				'Change password' => 'admin/setting/change_password',
			),
			'Site' => '/',
		),
		'insecure_global_head' => array(
			'Top' => 'admin/',
			'Site' => '/',
		),
		'secure_user_dropdown' => array(
			'Sign out' => 'admin/logout',
		),
		'insecure_user_dropdown' => array(
			'Sign in' => 'admin/login',
		),
		'global_side' => array(
			'Top' => 'admin/',
			'Site' => '/',
		),
		'secure_side' => array(
			'Member' => 'admin/member',
			'Settings' => 'admin/setting',
		),
	),
);
