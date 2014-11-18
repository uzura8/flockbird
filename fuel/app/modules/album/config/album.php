<?php

return array(
	'isDisplayOriginalFileName' => true,
	'articles' => array(
		'limit' => 3,
		'limit_max' => 5,
		'trim_width' => array(
			'name' => 70,
			'body'  => 500,
			'subinfo'  => 50,
		),
		'comment' => array(
			'limit' => 2,
			'limit_max' => 3,
			'trim_width' => 200,
		),
	),
	'display_setting' => array(
		'member' => array(
			'display_delete_link' => true,
		),
		'detail' => array(
			'display_upload_form' => true,
			'display_slide_image' => true,
		),
	),
);
