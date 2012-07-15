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
	),
);
