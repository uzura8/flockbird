<?php
return array(
	'articles' => array(
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
	),
	'types' => array(
		'normal' => 1,
		'group' => 2,
		'site_info' => 8,
		'system_info' => 9,
	),
);
