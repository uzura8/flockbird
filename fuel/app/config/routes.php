<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2018 Fuel Development Team
 * @link       https://fuelphp.com
 */

$routes = array(
	/**
	 * -------------------------------------------------------------------------
	 *  Default route
	 * -------------------------------------------------------------------------
	 *
	 */

	'_root_'  => FBD_ROUTES_ROOT_PATH,  // The default route

	/**
	 * -------------------------------------------------------------------------
	 *  Page not found
	 * -------------------------------------------------------------------------
	 *
	 */

	'_404_'   => 'error/404',   // The main 404 route

	'^member/?$'  => FBD_ROUTES_MYHOME_PATH,
	'^member/(\d+)/relation/(follows|followers|friends)'  => 'member/relation/list/$2/$1',
	'^member/me'  => 'member/home/me',
	'member/(\d+)'  => 'member/home/$1',
	'member/profile/(\d+)'  => 'member/profile/index/$1',
	'member/profile/image/(\d+)'  => 'member/profile/image/index/$1',
	'^member/setting/email/(regist)'  => 'member/setting/email/index/$1',
);

return Site_Config::merge_module_configs($routes, 'routes');
