<?php
return array(
	'_root_'  => 'site/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
	
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),
	'birds/(((?!(size|watch_spot|life_place)).)+)'    => 'birds/detail/$1',
	'member/setting/email'    => 'member/setting_email',
	'member/setting/password'    => 'member/setting_password',
);
