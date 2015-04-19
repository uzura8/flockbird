<?php
namespace Fuel\Tasks;

/**
 * Task SetupDB
 */

class SetupDB
{
	private static $absolute_execute = false;
	private static $database;
	private static $charset;

	public function __construct($args = null)
	{
		self::$absolute_execute = \Cli::option('absolute_execute', false);
		self::$charset = \Config::get('db.default.charset') ?: 'utf8';
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupdb
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
			if ($result) $result = self::insert_default_data();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Setup db error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, 'setup db', sprintf('Setup db %s.', self::$database));
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupdb:reset
	 *
	 * @return string
	 */
	public static function reset($database = null, $charset = null)
	{
		try
		{
			$result = self::exexute_drop_db($database);
			if ($result) $result = self::exexute_create_db($database, $charset);
			if ($result) $result = self::exexute_install_db($database);
			if ($result) $result = self::insert_default_data();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Reset db error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, __FUNCTION__.' db', sprintf('Reset db %s.', self::$database));
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupdb:create
	 *
	 * @return string
	 */
	public static function create($database = null, $charset = null)
	{
		try
		{
			$result = self::exexute_create_db($database, $charset);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Create db error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, __FUNCTION__.' db', sprintf('Create db %s.', self::$database));
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupdb:drop
	 *
	 * @return string
	 */
	public static function drop($database)
	{
		try
		{
			$result = self::exexute_drop_db($database);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Drop db error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, __FUNCTION__.' db', sprintf('Drop db %s.', self::$database));
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupdb:drop
	 *
	 * @return string
	 */
	public static function insert_default_data()
	{
		\Config::load('db_fixture', 'db_fixture');
		try
		{
			$db_fixtures = \Config::get('db_fixture');
			$result = self::load_fixtures($db_fixtures);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Insert default data error: %s', $e->getMessage()), false);
		}

		return \Util_Task::output_result_message($result, __FUNCTION__.' db', 'Insert default data.');
	}

	private static function exexute_create_db($database = null, $charset = null)
	{
		if (!$database && !$database = \Util_Db::get_database_name())
		{
			throw new \FuelException('Database name is not set at configs.');
		}
		self::$database = $database;

		$if_not_exists = self::$absolute_execute || \Site_Util::check_is_dev_env();

		return \DBUtil::shell_exec_create_database(self::$database, $charset, $if_not_exists);
	}

	private static function exexute_drop_db($database = null)
	{
		if (!self::$absolute_execute && !\Site_Util::check_is_dev_env())
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
		$setup_sql_file = sprintf('%sdata/sql/setup/setup%s.sql', FBD_BASEPATH, self::$charset == 'utf8mb4' ? '_utf8mb4' : '');
		if (!\DBUtil::shell_exec_sql4file($setup_sql_file, $database)) return false;

		if (!$modules = \Module::loaded()) return true;

		foreach ($modules as $module => $path)
		{
			$setup_sql_file = $path.'data/sql/setup/setup.sql';
			if (!file_exists($setup_sql_file)) continue;
			if (!\DBUtil::shell_exec_sql4file($setup_sql_file, $database)) return false;
		}

		return true;
	}

	private static function load_fixtures($fixtures = array())
	{
		if (!$fixtures) return false;

		$i = 0;
		foreach ($fixtures as $model => $rows)
		{
			foreach ($rows as $props)
			{
				$obj = $model::forge($props);
				if ($obj->save()) $i++;
			}
		}

		return (bool)$i;
	}
}
/* End of file tasks/setup.php */
