<?php

return array(
	'term' => array(
		'album'       => 'アルバム',
		'album_image' => 'アルバム写真',
	),
	'article_list' => array(
		'limit' => 20,
		'trim_width' => 2000,
	),
	'upload_files' => array(
		'img' => array(
			'album_image' => array(
				'sizes' => array(
					'raw',
					'50x50',
					'200x200',
					'600x600',
				),
				'max_size' => '720x720',
			),
		),
	),
	'image' => array(
		'album_image' => array(
			'original' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/album/original',
			),
			'small' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/album/small',
				'width'  => 50,
				'height' => 50,
			),
			'medium' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/album/medium',
				'width'  => 180,
				'height' => 180,
			),
			'lerge' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/album/lerge',
				'width'  => 600,
				'height' => 600,
			),
		),
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
	),
);
