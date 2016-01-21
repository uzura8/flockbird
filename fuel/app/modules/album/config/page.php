<?php
return array(
	'site' => array(
		'index' => array(
			'slide' => array(
				'recentAlbumImage' => array(
					'isEnabled' => false,
					'sizeKey' => 'topslide',
					'displayCount' => 5,
					'displayCountAdditional' => 10,
					'cache' => array(
						'isEnabled' => true,
						'expir' => 60 * 60 * 6,
						'key'  => 'topslide_image_uris',
					),
				),
			),
		),
	),
);
