<?php
return array(
	'importanceLevel' => array(
		'isEnabled' => true,
		'commentCountRate' => 2,
		'levels' => array(
			'1' => 3,// level => point
			'2' => 8,
			'3' => 16,
			'4' => 30,
			'5' => 50,
		),
	),
	'articleUnfollow' => array(
		'isEnabled' => true,
	),
	'articles' => array(
		'cache' => array(
			'is_use'  => false,
			'expir' => 60 * 60 * 3,
			'prefix'  => 'tl_',
		),
		'limit' => 10,
		'limit_max' => 12,
		'trim_width' => array(
			//'title' => 88,
			'body'  => 500,
			'title_in_body' => 50,
		),
		'truncate_lines' => array(
			'body'  => 10,
		),
		'comment' => array(
			'limit' => 10,
			'limit_max' => 12,
			'trim_width' => 200,
		),
		'thumbnail' => array(
			'limit' => array(
				'default' => 6,
				'album_image_timeline' => 12,
			),
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
		'member_name' => 9,
	),
	'periode_to_update' => array(
		'album' => '1 day',
		'album_image' => '1 day',
		'member_name' => '60 minute',// 短いスパンの変更は上書きする
	),
	'display_setting' => array(
	),
	//'follow_timeline_limit_max' => 10,
);
