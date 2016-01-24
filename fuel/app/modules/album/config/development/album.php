<?php
return array(
	'articles' => array(
		'limit' => 3,
		'limit_max' => 15,
		'trim_width' => array(
			'name' => 20,
			'body'  => 50,
			'subinfo'  => 20,
		),
		'comment' => array(
			'limit' => 2,
			'limit_max' => 3,
			'trim_width' => 50,
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
		'slide' => array(
			'limit' => 10,
		),
		'image' => array(
			'detail' => array(
				'displayGallery' => array(
					'limitMax' => 10,
					'checkLoopMax' => 3,
				),
			),
		),
	),
);
