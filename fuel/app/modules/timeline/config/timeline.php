<?php

return array(
	'articles' => array(
		'limit' => 3,
		'limit_max' => 50,
		'trim_width' => array(
			//'title' => 88,
			'body'  => 500,
			'title_in_body' => 50,
		),
		'truncate_lines' => array(
			'body'  => 5,
		),
		'comment' => array(
			'limit' => 5,
			'limit_max' => 20,
			'trim_width' => 200,
		),
		'thumbnail' => array(
			'limit' => 3,
		),
	),
	'types' => array(
		'normal' => 1,
		'member_register' => 2,
		'profile_image' => 3,
		'note' => 4,
		'album' => 5,
		'album_image' => 6,
		'album_image_profile' => 7,
		'album_image_timeline' => 8,
	),
	'periode_to_update' => array(
		'album' => '10 minute',
		//'album' => '3 day',
	),
	'display_setting' => array(
	),
);
