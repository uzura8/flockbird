<?php
namespace Fuel\Tasks;

/**
 * Task DbSetter
 */

class DbSetter
{
	public function __construct()
	{
		if (!\Site_Util::check_is_develop_env())
		{
			throw new \FuelException('This task is not work at prod env.');
		}
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r development::dbsetter
	 *
	 * @return string
	 */
	public static function run($exec_mode = null)
	{
		$messages = array();
		try
		{
			$messages[] = self::drop_db();
			$messages[] = self::setup_db();
		}
		catch(\FuelException $e)
		{
			$messages[] = 'Error: '.$e->getMessage();
		}

		return implode(PHP_EOL, $messages);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r development::dbresetter:drop_db
	 *
	 * @return string
	 */
	public static function drop_db()
	{
		if (!$database = \Util_Db::get_database_name())
		{
			return 'Drop database error: Database name is not set at config.';
		}
		try
		{
			\DBUtil::shell_exec_drop_database($database);
		}
		catch(\Database_Exception $e)
		{
			return sprintf('Drop db error: %s', $e->getMessage());
		}

		return sprintf('Drop db.');
	}

	public static function create_database($database = null, $charset = null, $if_not_exists = true)
	{
		if (!$database = \Util_Db::get_database_name())
		{
			throw new FuelException('Database name is not set at config.');
		}

		return DBUtil::shell_exec_create_database($database, $charset, $if_not_exists);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r development::dbresetter:setup_db
	 *
	 * @return string
	 */
	public static function setup_db()
	{
		$setup_sql_file = PRJ_BASEPATH.'data/sql/setup/setup.sql';

		if (!$database = \Util_Db::get_database_name())
		{
			return 'Setup database error: Database name is not set at config.';
		}
		try
		{
			\DBUtil::shell_exec_create_database($database);
			\Util_Db::exec_db_command4file($setup_sql_file);
		}
		catch(\Database_Exception $e)
		{
			return sprintf('Setup db error: %s', $e->getMessage());
		}

		return sprintf('Setup database.');
	}
}

/* End of file tasks/dbsetter.php */
