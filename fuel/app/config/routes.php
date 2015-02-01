<?php
$routes = array(
	'_root_'  => 'site/index',  // The default route
	'_404_'   => 'error/404',   // The main 404 route
	
	'member/(\d+)'  => 'member/home/$1',
	'member/profile/(\d+)'  => 'member/profile/index/$1',
	'member/profile/image/(\d+)'  => 'member/profile/image/index/$1',
);

return Site_Config::merge_module_configs($routes, 'routes');
