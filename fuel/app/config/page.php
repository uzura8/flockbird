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
		),
	),
);

return Site_Util::merge_module_configs($config, 'page');
