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
			),
			'timeline' => array(
				'isEnabled' => true,
				'list' => array(
					'limit' => 3,
					'limit_max' => 5,
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
