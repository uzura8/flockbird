<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.7
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */


Autoloader::add_core_namespace('MyAuth');

Autoloader::add_classes(array(
	'MyAuth\\Auth_Login_Simpleauth' => __DIR__.'/classes/auth/login/simpleauth.php',
	'MyAuth\\Auth_Login_Uzuraauth'  => __DIR__.'/classes/auth/login/uzuraauth.php',
));
