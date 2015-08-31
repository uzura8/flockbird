<?php
return array(
	'album' => array(
		'isEnabled'  => 1,
	),
	'upload' => array(
		'types' => array(
			'img' => array(
				'types' => array(
					// album image
					'ai' => array(
						'sizes' => array(
							'S' => '80x80',
							'M' => '220x220',
							'N_M' => '400x300xc',
							'L' => '600x600',
							'thumbnail' => '320x320xc',
						),
						'sizes_tmp' => array(
							'S' => '80x80',
						),
						'additional_sizes' => array(
							'profile' => array(
								'P_SS' => '20x20xc',
								'P_S' => '30x30xc',
								'P_M' => '50x50xc',
								'P_ML' => '120x120xc',
								'P_L' => '180x180xc',
								'P_LL' => '400x300xc',
							),
							'note' => array(
								'N_M' => '400x300xc',
							),
						),
						'default_size' => '80x80',
						'max_size' => '720x720',
					),
				),
			),
		),
	),
	'batch' => array(
		'limit' => array(
			'delete' => array(
				'album' => 50,
				'album_image' => 10,
			),
		),
	),
	'service' => array(
		'facebook' => array(
			'shareDialog' => array(
				'album' => array(
					'isEnabled' => true,
					'autoPopupAfterCreated' => false,
				),
			),
		),
	),
);
