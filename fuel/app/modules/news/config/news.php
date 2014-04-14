<?php

return array(
	'category' => array(
		'isEnabled' => false,
	),
	'image' => array(
		'isEnabled' => true,
	),
	'view_params' => array(
		'site' => array(
			'list' => array(
				'limit' => 5,
				'limit_max' => 50,
				'trim_width' => array(
					'title' => 88,
					'body'  => 500,
				),
				'truncate_lines' => array(
					'body'  => 5,
				),
			),
		),
		'admin' => array(
			'list' => array(
				'limit' => 3,
				'limit_max' => 5,
				'trim_width' => array(
					'title' => 30,
					'body'  => 100,
				),
				'truncate_lines' => array(
					'body'  => 2,
				),
			),
		),
	),
	'display_setting' => array(
	),
);
