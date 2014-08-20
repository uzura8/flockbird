<?php

/**
 * DBUtil Class
 *
 * @package		Fuel
 * @category	Core
 * @author		Dan Horrigan
 */
class DBUtil extends Fuel\Core\DBUtil
{
	public static function shell_exec_create_database($database, $charset = null, $if_not_exists = true, $db = null)
	{
		$charset = static::process_charset($charset, true);
		$sql  = 'CREATE DATABASE';
		$sql .= $if_not_exists ? ' IF NOT EXISTS ' : ' ';
		$sql .= DB::quote_identifier($database, static::$connection).$charset;
		if ($charset) $sql .= ' '.$charset;

		return static::shell_exec_sql($sql);
	}

	public static function shell_exec_drop_database($database, $if_exists = true, $db = null)
	{
		$sql  = 'DROP DATABASE';
		$sql .= $if_exists ? ' IF EXISTS ' : ' ';
		$sql .= DB::quote_identifier($database, static::$connection);

		return static::shell_exec_sql($sql);
	}

	public static function shell_exec_sql($sql)
	{
		$command = sprintf("echo '%s' | %s", $sql, Util_Db::make_mysql_conect_command());

		if ($error = Util_Toolkit::shell_exec($command))
		{
			throw new Database_Exception($error);
		}

		return true;
	}
}
