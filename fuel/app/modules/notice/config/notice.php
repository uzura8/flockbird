<?php
return array(
	'mention' => array(
		'isEnabled'  => true,
	),
	'cache' => array(
		'unreadCount' => array(
			'isEnabled'  => true,
			'expir' => 60 * 30,// 30 min
			'prefix'  => 'notice_unread_count_',
		),
	),
	'articles' => array(
		'limit' => 10,
		'limit_max' => 12,
	),
	'modalArticles' => array(
		'limit' => 5,
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
	),
	'display_setting' => array(
	),
);
