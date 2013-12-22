<?php

return array(
	'upload' => array(
		'types' => array(
			'img' => array(
				'types' => array(
					// album image
					'ai' => array(
						'sizes' => array(
							'S' => '80x80',
							'M' => '220x220',
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
);
