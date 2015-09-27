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
		'member' => array(
			'list' => array(
				'limit' => 20,
				'limit_max' => 50,
			),
		),
	),
);
