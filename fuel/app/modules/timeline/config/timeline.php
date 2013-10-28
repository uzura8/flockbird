<?php

return array(
	'articles' => array(
		'limit' => 3,
		'limit_max' => 50,
		'trim_width' => array(
			//'title' => 88,
			'body'  => 500,
		),
		'truncate_lines' => array(
			'body'  => 3,
		),
		'comment' => array(
			'limit' => 3,
			'trim_width' => 200,
		),
	),
	'types' => array(
		'normal' => 1,
		'member_register' => 2,
		'profile_image' => 3,
	),
	'display_setting' => array(
	),
);
