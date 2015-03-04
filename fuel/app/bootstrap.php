<?php

// Load original setting file.
require APPPATH.'config.inc.php';

// Bootstrap the framework DO NOT edit this
require COREPATH.'bootstrap.php';


Autoloader::add_classes(array(
	// Add classes you want to override here
	// Example: 'View' => APPPATH.'classes/view.php',
	'Controller' => APPPATH.'classes/controller.php',
	//'DB' => APPPATH.'classes/db.php',
	'Database_Query_Builder_Update' => APPPATH.'classes/database/query/builder/update.php',
	'DBUtil' => APPPATH.'classes/dbutil.php',
	'Uri' => APPPATH.'classes/uri.php',
	'Str' => APPPATH.'classes/str.php',
	'Html' => APPPATH.'classes/html.php',
	'Validation' => APPPATH.'classes/validation.php',
	'Agent' => APPPATH.'classes/agent.php',
	'Fieldset_Field' => APPPATH.'classes/fieldset/field.php',
));

// Register the autoloader
Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
Fuel::$env = (isset($_SERVER['FUEL_ENV']) ? $_SERVER['FUEL_ENV'] : constant('Fuel::'.PRJ_ENVIRONMENT));

// Initialize the framework with the config file.
Fuel::init('config.php');

// include helpers.
Util_toolkit::include_php_files(APPPATH.'helpers');


// Config load.
Config::load('site', 'site');
Config::load('term', 'term');
Config::load('icon', 'icon');
Config::load('page', 'page');
Config::load('template', 'template');
Config::load('exif', 'exif');
// Config of each module load.
$modules = Module::loaded();
foreach ($modules as $module => $path)
{
	if (file_exists(sprintf('%sconfig/%s.php', $path, $module)))
	{
		Config::load(sprintf('%s::%s', $module, $module), $module);
	}
}
// Config of navigation load.
Config::load('navigation', 'navigation');

if (in_array(PRJ_ENVIRONMENT, array('DEVELOPMENT', 'TEST')))
{
	Config::load('develop', 'develop');
}
Site_Config::regulate_configs_for_module_loaded();
