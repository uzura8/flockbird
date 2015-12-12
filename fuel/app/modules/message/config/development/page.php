<?php
return array(
	'site' => array(
		'viewParams' => array(
			'message' => array(
				'list' => array(
					'limit' => 3,
					'limitMax' => 5,
					'trimWidth' => array(
						'title' => 8,
						'body'  => 20,
					),
					'truncateLines' => array(
						'body'  => 3,
					),
				),
				'mordal' => array(
					'limit' => 3,
					'limitMax' => 5,
				),
			),
		),
	),
	'admin' => array(
		'viewParams' => array(
			'message' => array(
				'list' => array(
					'trimWidth' => array(
						'title' => 50,
						'body'  => 100,
					),
					'truncateLines' => array(
						'body'  => 2,
					),
				),
			),
		),
	),
);

