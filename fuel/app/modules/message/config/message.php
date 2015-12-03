<?php
return array(
	'articles' => array(
		'nl2br' => true,
		'limit' => 20,
		'limit_max' => 30,
		'trim_width' => array(
			//'subject' => 88,
			'body'  => 500,
		),
		'truncate_lines' => array(
			'body'  => 10,
		),
	),
	'types' => array(
		'member' => 1,
		'group' => 2,
		'site_info' => 8,
		'system_info' => 9,
	),
	'modalArticles' => array(
		'limit' => 5,
	),
);
