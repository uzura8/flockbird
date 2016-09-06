<?php

return array(
	'user' => array(
		'acceptedGroup' => array(
			\Admin\Site_AdminUser::GROUP_USER,
			\Admin\Site_AdminUser::GROUP_MODERATOR,
			\Admin\Site_AdminUser::GROUP_ADMIN,
		),
	),
	'articles' => array(
		'images' => array(
			'limit' => 9,
			'limit_max' => 12,
			'trim_width' => array(
				'name' => 70,
			),
		),
	),
	'member' => array(
		'group' => array(
			'display' => array(
				'isEnabled' => false,
			),
			'edit' => array(
				'isEnabled' => false,
				'roles' => array(
					'admin' => true,
					'moderator' => array('user', 'specified_user'),
					'user' => array(),
				),
			),
		),
		'inviteFromAdmin' => array(
			'isEnabled'  => false,
			'selectGroup' => array(
				'isEnabled'  => false,
				'options' => array(
					'specified_user',
					'user',
				),
			),
		),
	),
);
