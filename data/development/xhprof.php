<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.7
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

// xhprof setting
define('FBD_XHPROF_PROFILING', true);
define('FBD_XHPROF_LIB_PATH', '/var/www/html/xhprof_lib');
define('FBD_XHPROF_URL', 'http://dev.example.com/xhprof_html');

if (FBD_XHPROF_PROFILING)
{
	include_once FBD_XHPROF_LIB_PATH.'/utils/xhprof_lib.php';
	include_once FBD_XHPROF_LIB_PATH.'/utils/xhprof_runs.php';
	$GLOBALS['xhprof_data'] = '';
}
if (FBD_XHPROF_PROFILING) xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);// start profiling


/**
 * Set error reporting and display errors settings.  You will want to change these when in production.
 */
error_reporting(-1);
ini_set('display_errors', 1);

/**
 * Website document root
 */
define('DOCROOT', __DIR__.DIRECTORY_SEPARATOR);

/**
 * Path to the application directory.
 */
define('APPPATH', realpath(__DIR__.'/../fuel/app/').DIRECTORY_SEPARATOR);

/**
 * Path to the default packages directory.
 */
define('PKGPATH', realpath(__DIR__.'/../fuel/packages/').DIRECTORY_SEPARATOR);

/**
 * The path to the framework core.
 */
define('COREPATH', realpath(__DIR__.'/../fuel/core/').DIRECTORY_SEPARATOR);

// Get the start time and memory for use later
defined('FUEL_START_TIME') or define('FUEL_START_TIME', microtime(true));
defined('FUEL_START_MEM') or define('FUEL_START_MEM', memory_get_usage());

// Load in the Fuel autoloader
require COREPATH.'classes'.DIRECTORY_SEPARATOR.'autoloader.php';
class_alias('Fuel\\Core\\Autoloader', 'Autoloader');

// Boot the app
require APPPATH.'bootstrap.php';

// Generate the request, execute it and send the output.
try
{
	$response = Request::forge()->execute()->response();
}
catch (HttpNotFoundException $e)
{
	\Request::reset_request(true);

	$route = array_key_exists('_404_', Router::$routes) ? Router::$routes['_404_']->translation : Config::get('routes._404_');

	if($route instanceof Closure)
	{
		$response = $route();

		if( ! $response instanceof Response)
		{
			$response = Response::forge($response);
		}
	}
	elseif ($route)
	{
		$response = Request::forge($route, false)->execute()->response();
	}
	else
	{
		throw $e;
	}
}

// Render the output
$response->body((string) $response);

// This will add the execution time and memory usage to the output.
// Comment this out if you don't use it.
if (strpos($response->body(), '{exec_time}') !== false or strpos($response->body(), '{mem_usage}') !== false)
{
	$bm = Profiler::app_total();
	$response->body(
		str_replace(
			array('{exec_time}', '{mem_usage}'),
			array(round($bm[0], 4), round($bm[1] / pow(1024, 2), 3)),
			$response->body()
		)
	);
}

$response->send(true);


if (FBD_XHPROF_PROFILING) $GLOBALS['xhprof_data'] = xhprof_disable();//stop profiler
if (FBD_XHPROF_PROFILING)
{
	$xhprof_runs = new XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($GLOBALS['xhprof_data'], '');
	$prof_url = sprintf('%s/index.php?run=%s&source=', FBD_XHPROF_URL, $run_id);
	echo '<div class="well well-sm">';
	echo sprintf('<a href="%s" target="_blank">See xhprof report</a>', $prof_url);
	echo '</div>';
}
