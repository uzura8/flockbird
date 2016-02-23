<?php
$config = array(
	'site' => array(
		'common' => array(
			'thumbnailModalLink' => array(
				'isEnabled' => true,
			),
			'shareButton' => array(
				'isEnabled' => false,
				'twitter' => array(
					'isEnabled' => true,
				),
				'facebook' => array(
					'share' => array(
						'isEnabled' => true,
					),
					//'like' => array(
					//	'isEnabled' => true,
					//),
				),
				'google' => array(
					'isEnabled' => true,
				),
				'line' => array(
					'isEnabled' => true,
				),
			),
		),
		'navbar' => array(
			'notification' => array(
				'cache' => array(
					'common' => array(
						'unreadCount' => array(
							'expir' => 60 * 30,// 30 min
							'prefix'  => 'notice_unread_count_',
						),
					),
				),
			),
			'request' => array(
				'isEnabled' => false,
			),
		),
		'index' => array(
			'slide' => array(
				'isEnabled' => true,
				'interval' => 30000,
				'title' => '',
				'site_lead' => '',
				'isDisplayRegisterBtn' => true,
				'images' => array(
					'assets/img/site/sample/01.jpg',
					'assets/img/site/sample/02.jpg',
					'assets/img/site/sample/03.jpg',
				),
			),
			'timeline' => array(
				'isEnabled' => true,
				'list' => array(
					'limit' => 3,
					'limit_max' => 5,
				),
			),
			'news' => array(
				'isEnabled' => false,
				'list' => array(
					'limit' => 5,
					'limit_max' => 10,
				),
			),
			'albumImage' => array(
				'isEnabled' => true,
				'list' => array(
					'limit' => 10,
					'limit_max' => 10,
				),
			),
		),
	),
);

return Site_Config::merge_module_configs($config, 'page');
