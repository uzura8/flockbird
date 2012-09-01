<?php

return array(
	'upload_files' => array(
		'img' => array(
			// album image
			'ai' => array(
				'sizes' => array(
					'raw',
					'80x80',
					'200x200',
					'600x600',
				),
				'max_size' => '720x720',
			),
		),
	),
/*
		'image_versions' => array(
			'small' => array(
				'upload_dir' => PRJ_UPLOAD_DIR.'/img/album/small/',
				'upload_url' => Uri::create('upload/img/album/small/'),
				'max_width'  => 80,
				'max_height' => 80,
			),
			'thumbnail' => array(
				'upload_dir' => PRJ_UPLOAD_DIR.'/img/album/thumbnails/',
				'upload_url' => Uri::create('upload/img/album/thumbnails/'),
				'max_width'  => 100,
				'max_height' => 100,
			),
			'medium' => array(
				'upload_dir' => PRJ_UPLOAD_DIR.'/img/album/medium/',
				'upload_url' => Uri::create('upload/img/album/medium/'),
				'max_width'  => 250,
				'max_height' => 250,
			),
			'lerge' => array(
				'upload_dir' => PRJ_UPLOAD_DIR.'/img/album/lerge/',
				'upload_url' => Uri::create('upload/img/album/lerge/'),
				'max_width'  => 600,
				'max_height' => 600,
			),
		),

*/
);
