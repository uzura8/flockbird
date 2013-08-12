<?php

return array(
	'articles' => array(
		'limit' => 2,
		'trim_width' => array(
			'title' => 90,
			'body'  => 500,
		),
		'comment' => array(
			'limit' => 5,
			'trim_width' => 200,
		),
	),
	'display_setting' => array(
		'form' => array(
			'upload' => array(
				'display' => true,
				'type' => 'multiple', // simple or multiple
			),
			'tmp_images' => array(
				'image_position_radio_button' => false,
			),
			'modal' => array(
				'display_delete_button' => true,
			),
		),
	),
);
