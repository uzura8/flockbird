<?php
return array(
	'articles' => array(
		'limit' => 2,
		'limit_max' => 3,
		//'trim_width' => array(
		//	//'title' => 88,
		//	'body'  => 500,
		//	'title_in_body' => 50,
		//),
		'truncate_lines' => array(
			'body'  => 3,
		),
		'comment' => array(
			'limit' => 2,
			'limit_max' => 3,
		//	'trim_width' => 200,
		),
		'thumbnail' => array(
			'limit' => array(
				'default' => 3,
				'album_image_timeline' => 3,
			),
		),
	),
	'periode_to_update' => array(
		'album' => '1 minute',
		'album_image' => '1 minute',
		'member_name' => '1 minute',// 短いスパンの変更は上書きする
	),
	//'follow_timeline_limit_max' => 10,
);
