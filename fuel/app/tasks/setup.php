<?php
namespace Fuel\Tasks;

/**
 * Task Setup
 */

class Setup
{
	private static $absolute_execute = false;
	private static $database;

	public function __construct($args = null)
	{
		self::$absolute_execute = \Cli::option('absolute_execute', false);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setup
	 *
	 * @return string
	 */
	public static function run($database = null, $charset = null)
	{
		try
		{
			self::$absolute_execute = false;
			$result = self::exexute_create_db($database, $charset);
			if ($result) $result = self::exexute_install_db($database);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Setup db error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, 'setup', sprintf('Setup db %s.', self::$database));
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setup:reset_db
	 *
	 * @return string
	 */
	public static function reset_db($database = null, $charset = null)
	{
		try
		{
			$result = self::exexute_drop_db($databasecharset);
			if ($result) $result = self::exexute_create_db($database, $charset);
			if ($result) $result = self::exexute_install_db($database);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Reset db error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, __FUNCTION__, sprintf('Reset db %s.', self::$database));
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setup:create_db
	 *
	 * @return string
	 */
	public static function create_db($database = null, $charset = null)
	{
		try
		{
			$result = self::exexute_create_db($database, $charset);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Create db error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, __FUNCTION__, sprintf('Create db %s.', self::$database));
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setup:drop_db
	 *
	 * @return string
	 */
	public static function drop_db($database)
	{
		try
		{
			$result = self::exexute_drop_db($database);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Drop db error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, __FUNCTION__, sprintf('Drop db %s.', self::$database));
	}

	private static function exexute_create_db($database = null, $charset = null)
	{
		if (!$database && !$database = \Util_Db::get_database_name())
		{
			throw new \FuelException('Database name is not set at configs.');
		}
		self::$database = $database;

		$if_not_exists = self::$absolute_execute || \Site_Util::check_is_develop_env();

		return \DBUtil::shell_exec_create_database(self::$database, $charset, $if_not_exists);
	}

	private static function exexute_drop_db($database = null)
	{
		if (!self::$absolute_execute && !\Site_Util::check_is_develop_env())
		{
			throw new \FuelException('This task is not work at prod env.');
		}
		if (!$database && !$database = \Util_Db::get_database_name())
		{
			throw new \FuelException('Drop db error: Database name is not set at configs.');
		}
		self::$database = $database;

		return \DBUtil::shell_exec_drop_database(self::$database);
	}

	private static function exexute_install_db($database = null)
	{
		$setup_sql_file = PRJ_BASEPATH.'data/sql/setup/setup.sql';

		return \DBUtil::shell_exec_sql4file($setup_sql_file, $database);
	}
}
/* End of file tasks/setup.php */
