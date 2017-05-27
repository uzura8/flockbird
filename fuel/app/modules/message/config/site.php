<?php
return array(
	'message' => array(
		'isEnabled'  => 1,
	),
	'member_config_default' => array(
		'notice_messageMailMode' => 1,
	),
	'upload' => array(
		'types' => array(
			'img' => array(
				'types' => array(
					'ms' => array(
						'isOriginalTable' => true,
						'sizes' => array(
							'S' => '80x80',
							'M' => '800x800',
							'N_M' => '400x300xc',
							'L' => '1000x1000',
							'thumbnail' => '320x320xc',
						),
						'sizes_tmp' => array(
							'S' => '80x80',
						),
						'default_size' => '80x80',
						'max_size' => '720x720',
					),
				),
			),
		),
	),
	'batch' => array(
		'limit' => array(
			'delete' => array(
				'message' => 50,
			),
		),
	),
);
