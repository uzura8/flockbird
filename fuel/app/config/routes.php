<?php
$routes = array(
	'_root_'  => 'site/index',  // The default route
	'_404_'   => 'error/404',   // The main 404 route
	
	'^member/(\d+)/relation/(follows|followers|friends)'  => 'member/relation/list/$2/$1',
	'^member/me'  => 'member/home/me',
	'member/(\d+)'  => 'member/home/$1',
	'member/profile/(\d+)'  => 'member/profile/index/$1',
	'member/profile/image/(\d+)'  => 'member/profile/image/index/$1',
	'^member/setting/email/(regist)'  => 'member/setting/email/index/$1',
);

return Site_Config::merge_module_configs($routes, 'routes');
