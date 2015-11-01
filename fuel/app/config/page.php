<?php
$config = array(
	'site' => array(
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
