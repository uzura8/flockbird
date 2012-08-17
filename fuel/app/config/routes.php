<?php
$routes = array(
	'_root_'  => 'site/index',  // The default route
	'_404_'   => 'error/404',   // The main 404 route
	
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),
	'birds/(((?!(size|watch_spot|life_place)).)+)'    => 'birds/detail/$1',
	'member/(\d+)'  => 'member/home/$1',
);

$modules = Module::loaded();
foreach ($modules as $module => $path)
{
	Config::load($module.'::routes', $module.'_routes');
	$module_routes = Config::get($module.'_routes');
	if (!empty($module_routes)) $routes += $module_routes;
}

return $routes;
