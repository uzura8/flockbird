<?php
return array(
	'site' => array(
		'navbar' => array(
			'notification' => array(
				'cache' => array(
					'message' => array(
						'unreadCount' => array(
							'isEnabled'  => true,
							'prefix'  => 'message_unread_count_',
						),
					),
				),
			),
		),
		'viewParams' => array(
			'message' => array(
				'list' => array(
					'limit' => 20,
					'limitMax' => 30,
					'nl2br' => true,
					'trimWidth' => array(
						'title' => 88,
						'body'  => 500,
					),
					'truncateLines' => array(
						'body'  => 10,
					),
				),
			),
			'mordal' => array(
				'limit' => 5,
				'limitMax' => 8,
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

