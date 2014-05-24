<?php

return array(
	'max_articles_per_day' => 50,
	'category' => array(
		'isEnabled' => true,
	),
	'image' => array(
		'isEnabled' => true,
	),
	'viewParams' => array(
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
				'limit' => 5,
				'limit_max' => 10,
				'trim_width' => array(
					'title' => 50,
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
