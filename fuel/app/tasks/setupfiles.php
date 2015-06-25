<?php
namespace Fuel\Tasks;

/**
 * Task SetupFiles
 */

class SetupFiles
{
	private static $absolute_execute = false;

	public function __construct($args = null)
	{
		self::$absolute_execute = \Cli::option('absolute_execute', false);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupfiles
	 *
	 * @return string
	 */
	public static function run()
	{
		try
		{
			self::$absolute_execute = false;
			$result = self::update_htaccess();
			if ($result) $result = self::update_less_setting();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Setup file error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, '', 'Setup files: .htaccess, less setting');
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupfiles:htaccess
	 *
	 * @return string
	 */
	public static function update_htaccess()
	{
		try
		{
			$result = self::change_rewrite_base();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message($e->getMessage(), false);
		}

		return \Util_Task::output_result_message($result, __FUNCTION__, 'Update .htaccess');
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupfiles:less_setting
	 *
	 * @return string
	 */
	public static function update_less_setting()
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
	 * change_rewrite_base
	 */
	private static function change_rewrite_base($output_error_with_nochange = false)
	{
		$file_rewriter = new \Util_FileRewriter(DOCROOT.'.htaccess');
		if (!$file_rewriter->replace_lines('#^(\s+RewriteBase\s+)([/0-9a-zA-Z\-_]+)$#', '$1'.FBD_URI_PATH, true)
				&& $output_error_with_nochange)
		{
			throw new \FuelException('Setup file error: RewriteBase not changed');
		}

		return true;
	}

	/**
	 * change_rewrite_base
	 */
	private static function change_less_setting($output_error_with_nochange = false)
	{
		$file_rewriter = new \Util_FileRewriter(APPPATH.'assets/less/env_variables.less');

		if (!$file_rewriter->replace_lines('#^(@document\-root\-path:\s+)"([/0-9a-zA-Z\-_]+)";$#', sprintf('$1"%s";', FBD_URI_PATH), true)
				&& $output_error_with_nochange)
		{
			throw new \FuelException('Setup file error: @document-root-path not changed');
		}

		return true;
	}
}
/* End of file tasks/setupfiles.php */

