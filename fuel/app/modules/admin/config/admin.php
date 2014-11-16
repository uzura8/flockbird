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
			'limit' => 3,
			'limit_max' => 5,
			'trim_width' => array(
				'name' => 70,
			),
		),
	),
);
