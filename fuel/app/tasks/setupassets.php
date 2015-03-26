<?php
namespace Fuel\Tasks;

/**
 * Task SetupAssets
 */

class SetupAssets
{
	private static $absolute_execute = false;

	public function __construct($args = null)
	{
		self::$absolute_execute = \Cli::option('absolute_execute', false);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupassets
	 *
	 * @return string
	 */
	public static function run()
	{
		try
		{
			self::$absolute_execute = false;
			$result_message = self::compile_less();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Deploy error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message(true, $result_message);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupassets:less
	 *
	 * @return string
	 */
	public static function less()
	{
		try
		{
			$result_message = self::compile_less();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Compile less error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message(true, $result_message);
	}

	/**
	 * compile less
	 */
	private static function compile_less()
	{
		$return_message = '';
		$configs = \Config::get('less.less_source_files');
		foreach ($configs as $config)
		{
			\Asset::less($config, array(), null, false, true);
			$return_message .= 'Compile '.$config.PHP_EOL;
		}

		return $return_message;
	}
}
/* End of file tasks/deploy.php */
