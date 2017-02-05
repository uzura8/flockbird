<?php

// Load original setting file.
require APPPATH.'config.inc.php';

// Bootstrap the framework DO NOT edit this
require COREPATH.'bootstrap.php';

\Autoloader::add_classes(array(
	// Add classes you want to override here
	// Example: 'View' => APPPATH.'classes/view.php',
	'Controller' => APPPATH.'classes/controller.php',
	'Database_Query_Builder_Update' => APPPATH.'classes/database/query/builder/update.php',
	'DBUtil' => APPPATH.'classes/dbutil.php',
	'Uri' => APPPATH.'classes/uri.php',
	'Lang' => APPPATH.'classes/lang.php',
	'Str' => APPPATH.'classes/str.php',
	'Html' => APPPATH.'classes/html.php',
	'Validation' => APPPATH.'classes/validation.php',
	'Agent' => APPPATH.'classes/agent.php',
	'Asset' => APPPATH.'classes/asset.php',
	'Fieldset' => APPPATH.'classes/fieldset.php',
	'Fieldset_Field' => APPPATH.'classes/fieldset/field.php',
	'Inflector' => APPPATH.'classes/inflector.php',
));

// Register the autoloader
\Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
\Fuel::$env = \Arr::get($_SERVER, 'FUEL_ENV', \Arr::get($_ENV, 'FUEL_ENV', strtolower(FBD_ENVIRONMENT)));

// include helpers.
Util_toolkit::include_php_files(APPPATH.'helpers');

// Initialize the framework with the config file.
\Fuel::init('config.php');

// Config load.
Config::load('site', 'site');
Config::load('member', 'member');
Config::load('i18n', 'i18n');
// Load lang fils
Site_Lang::configure_lang(false, false, null, true);

Config::load('less', 'less');
if (IS_TASK)
{
	Config::load('task', 'task');
}
else
{
	Config::load('icon', 'icon');
	Config::load('page', 'page');
	Config::load('exif', 'exif');
}

// Load each module files.
$modules = Site_Util::get_active_modules();
foreach ($modules as $module => $path)
{
	// Load module configs
	if (file_exists(sprintf('%sconfig/%s.php', $path, $module)))
	{
		Config::load(sprintf('%s::%s', $module, $module), $module);
	}
	// Include module helpers.
	Util_toolkit::include_php_files(sprintf('%smodules/%s/helpers', APPPATH, $module));
}
// Config of navigation load.
if (!IS_TASK)
{
	Config::load('navigation', 'navigation');
}

if (in_array(FBD_ENVIRONMENT, array('DEVELOPMENT', 'TEST')))
{
	Config::load('develop', 'develop');
}
Site_Config::regulate_configs_for_module_loaded();


// Register the autoloader for library
if (FBD_AWS_ACCESS_KEY && FBD_AWS_SECRET_KEY && FBD_AWS_S3_BUCKET)
{
	Autoloader::add_namespace('Aws', APPPATH.'vendor/aws/aws-sdk-php/src/Aws', true);
}
if (conf('library.goutte.isEnabled'))
{
	$goutte_path = FBD_BASEPATH.'fuel/vendor/fabpot/goutte/Goutte/';
	Autoloader::add_namespace('Goutte', $goutte_path, true);
	Autoloader::add_class('Client', $goutte_path.'Client.php');
}

