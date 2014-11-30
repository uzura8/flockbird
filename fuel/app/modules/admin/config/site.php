<?php
return array(
	'login_uri' => array(
		'admin' => 'admin/login',
	),
	'original_user_id' => array(
		'admin'  => 1,
	),
	'mail' => array(
		'admin' => array(
			'from_name' => '管理者',
			'from_email' => PRJ_ADMIN_MAIL,
		),
	),
	'upload' => array(
		'types' => array(
			'img' => array(
				'types' => array(
					'si' => array(
						'sizes' => array(
							'S' => '80x80',
							'M' => '220x220',
							'N_M' => '400x300xc',
							'L' => '600x600',
							'thumbnail' => '320x320xc',
						),
						'sizes_tmp' => array(
							'S' => '80x80',
						),
					),
				),
			),
		),
	),
);
