<?php
return array(
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
		'timeline_commented' => 1,
		'timeline_liked' => 2,
		'timeline_comment_liked' => 3,
		'note_commented' => 4,
		'note_liked' => 5,
		'note_comment_liked' => 6,
		'album_image_commented' => 7,
		'album_image_liked' => 8,
		'album_image_comment_liked' => 9,
	),
	'periode_to_update' => array(
		'album' => '1 day',
		'album_image' => '1 day',
		'member_name' => '60 minute',// 短いスパンの変更は上書きする
	),
	'display_setting' => array(
	),
);
