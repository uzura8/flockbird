<?php

return array(
	'articles' => array(
		'limit' => 3,
		'trim_width' => array(
			'name' => 70,
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
			'display_upload_form' => true,
			'display_slide_image' => true,
		),
		'upload' => array(
			'display_uploaded_files' => false,
			'display_delete_button' => false,
		),
	),
);
