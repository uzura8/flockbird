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
		'limit_max' => 5,
	),
	'modalArticles' => array(
		'limit' => 2,
	),
	'noticeMemberFrom' => array(
		'limit' => 2,
	),
	'types' => array(
		'create' => 1,
		'update' => 2,
		'comment' => 3,
		'like' => 4,
		'child_data' => 5,
	),
	'periode_to_update' => array(
		'default' => '6 hours',
		//'default' => '1 minute',
	),
	'display_setting' => array(
	),
);
