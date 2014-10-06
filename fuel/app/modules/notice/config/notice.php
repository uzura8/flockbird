<?php
return array(
	'cache' => array(
		'unreadCount' => array(
			'isEnabled'  => true,
			'expir' => 60 * 30,// 30 min
			'prefix'  => 'notice_unread_count_',
		),
	),
	'articles' => array(
		'limit' => 2,
		'limit_max' => 3,
		'trim_width' => array(
			//'title' => 88,
			'body'  => 500,
			'title_in_body' => 50,
		),
		'truncate_lines' => array(
			'body'  => 5,
		),
	),
	'types' => array(
		'create' => 1,
		'update' => 2,
		'comment' => 3,
		'like' => 4,
	),
	'periode_to_update' => array(
		'default' => '6 hours',
	),
	'display_setting' => array(
	),
);
