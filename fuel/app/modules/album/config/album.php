<?php

return array(
	'articles' => array(
		'limit' => 20,
		'trim_width' => array(
			'name' => 100,
			'body'  => 500,
			'subinfo'  => 50,
		),
		'comment' => array(
			'limit' => 5,
			'trim_width' => 200,
		),
	),
	'display_setting' => array(
		'member' => array(
			'display_delete_link' => false,
		),
		'detail' => array(
			'display_upload_form' => false,
			'display_slide_image' => false,
		),
		'upload' => array(
			'display_delete_button' => false,
		),
	),
);
