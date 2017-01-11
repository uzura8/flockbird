<?php
namespace Fuel\Tasks;

/**
 * Task SetupLang
 */

class SetupLang
{
	private static $absolute_execute = false;

	public function __construct($args = null)
	{
		self::$absolute_execute = \Cli::option('absolute_execute', false);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setuplang
	 *
	 * @return string
	 */
	public static function run()
	{
		try
		{
			self::$absolute_execute = false;
			$file_count = self::output_lang_js();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Setup lang error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message((bool)$file_count, '', sprintf('Setup files: Output %d lang files', $file_count));
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupfiles:output_lang_js
	 *
	 * @return string
	 */
	public static function lang_js()
	{
		try
		{
			$result = self::change_less_setting();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message($e->getMessage(), false);
		}

		return true;
	}

	/**
	 * output js lang files
	 */
	private static function output_lang_js()
	{
		$save_dir_path = DOCROOT.'assets/cache/lang/';
		$languages = array_keys(conf('lang.options', 'i18n'));
		$i = 0;
		foreach ($languages as $language)
		{
			\Site_Lang::reset_lang($language, false);
			if (! $lines  = \Lang::get_all($language)) continue;
			if (! $output = json_encode($lines)) continue;

			if (! file_exists($save_dir_path)) \Util_File::make_dir($save_dir_path);
			$save_file_path = sprintf('%s%s.json', $save_dir_path, $language);
			if (file_put_contents($save_file_path, $output)) $i++;
		}

		return $i;
	}
}
/* End of file tasks/setuplang.php */

