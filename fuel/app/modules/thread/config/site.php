<?php
return array(
	'thread' => array(
		'isEnabled'  => 1,
	),
	'batch' => array(
		'limit' => array(
			'delete' => array(
				'thread' => 30,
			),
		),
	),
	'upload' => array(
		'types' => array(
			'img' => array(
				'types' => array(
					't' => array(
						'sizes' => array(
							'M' => '220x220',
							'L' => '600x600',
							'thumbnail' => '220x220xc',
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
);
