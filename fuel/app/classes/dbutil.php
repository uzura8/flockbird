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
		$charset = self::process_charset($charset, true);
		$sql  = 'CREATE DATABASE';
		$sql .= $if_not_exists ? ' IF NOT EXISTS ' : ' ';
		$sql .= DB::quote_identifier($database, static::$connection);
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

	public static function shell_exec_sql4file($sql_file, $dbname = null)
	{
		$command = sprintf('%s < %s', Util_Db::make_mysql_conect_command(true, $dbname), $sql_file);
		if ($error = Util_Toolkit::shell_exec($command))
		{
			throw new Database_Exception($error);
		}

		return true;
	}

	protected static function process_charset($charset = null, $is_default = false, $collation = null)
	{
		$charset or $charset = Util_Db::get_conection_configs('charset');

		if (empty($charset))
		{
			return '';
		}

		$collation or $collation = Util_Db::get_conection_configs('collation');

		if (empty($collation) and ($pos = stripos($charset, '_')) !== false)
		{
			$collation = $charset;
			$charset = substr($charset, 0, $pos);
		}

		$charset = 'CHARACTER SET '.$charset;

		if ($is_default)
		{
			$charset = 'DEFAULT '.$charset;
		}

		if ( ! empty($collation))
		{
			if ($is_default)
			{
				$charset .= ' DEFAULT';
			}
			$charset .= ' COLLATE '.$collation;
		}

		return $charset;
	}
}
