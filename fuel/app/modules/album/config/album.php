<?php

return array(
	'isDisplayOriginalFileName' => true,
	'albumImageLoction' => array(
		'saveFromExif' => true,
	),
	'articles' => array(
		'limit' => 9,
		'limit_max' => 12,
		'trim_width' => array(
			'name' => 70,
			'body'  => 500,
			'subinfo'  => 50,
		),
		'comment' => array(
			'limit' => 3,
			'limit_max' => 5,
			'trim_width' => 200,
		),
	),
	'display_setting' => array(
		'member' => array(
			'display_delete_link' => true,
		),
		'detail' => array(
			'display_upload_form' => false,
			'display_slide_image' => false,
			'displayMap' => true,
		),
		'edit_images' => array(
			'displayMap' => true,
		),
		'image' => array(
			'detail' => array(
				'displayMap' => true,
			),
			'edit' => array(
				'displayMap' => true,
			),
		),
	),
);
