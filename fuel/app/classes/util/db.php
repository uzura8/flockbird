<?php
class Util_Db
{
	public static function conv_col($rows)
	{
		$return = array();
		foreach ($rows as $row)
		{
			$return[] = array_shift($row);
		}

		return $return;
	}

	public static function conv_assoc($rows)
	{
		$return = array();
		foreach ($rows as $row)
		{
			$return[array_shift($row)] = array_shift($row);
		}

		return $return;
	}

	public static function get_syllabary_range_array($initial)
	{
		$syllabary_range_list = array(
			'ア' => array('ア%', 'カ%'),
			'カ' => array('カ%', 'サ%'),
			'サ' => array('サ%', 'タ%'),
			'タ' => array('タ%', 'ナ%'),
			'ナ' => array('ナ%', 'ハ%'),
			'ハ' => array('ハ%', 'マ%'),
			'マ' => array('マ%', 'ヤ%'),
			'ヤ' => array('ヤ%', 'ン%'),
		);

		if (empty($syllabary_range_list[$initial])) return false;

		return $syllabary_range_list[$initial];
	}

	public static function get_columns($table, $is_column_only = true)
	{
		$list_columns = DB::list_columns($table);
		if (!$is_column_only) return $list_columns;

		return array_keys($list_columns);
	}

	public static function get_conection_configs($key = null)
	{
		$db_conection_configs = Config::get('db.default.connection');
		if (empty($key)) return $db_conection_configs;
		if (!empty($db_conection_configs[$key])) return $db_conection_configs[$key];

		if (in_array($key, array('host', 'port', 'database')))
		{

			if (!empty($db_conection_configs['dsn']))
			{
				return self::get_dsn_config($db_conection_configs['dsn'], $key);
			}

			return null;
		}

		return null;
	}

	public static function get_dsn_config($dsn_str, $dns_key = null)
	{
		if (!preg_match('/(.+)\:(.+)/', $dsn_str, $matches))
		{
			return null;
		}
		if ($matches[1] != 'mysql') return null;

		$dsn_settings = explode(';', $matches[2]);
		$dsn_configs = array();
		foreach ($dsn_settings as $settings)
		{
			if (!preg_match('/(.+)=(.+)/', $settings, $matches))
			{
				continue;
			}
			$dsn_configs[$matches[1]] = $matches[2];
		}
		if (!$dsn_configs) return null;

		if (empty($dns_key)) return $dsn_configs;

		if ($dns_key == 'database') $dns_key = 'dbname';
		if (empty($dsn_configs[$dns_key]))
		{
			return null;
		}

		return $dsn_configs[$dns_key];
	}

	public static function get_database_name()
	{
		return self::get_conection_configs('database');
	}

	public static function exec_db_command4file($sql_file, $dbname = null)
	{
		try
		{
			$command = sprintf('%s < %s', Util_Db::make_mysql_conect_command(true, $dbname), $sql_file);
		}
		catch(FuelException $e)
		{
			throw new FuelException($e->getMessage());
		}
		if ($error = Util_Toolkit::shell_exec($command))
		{
			throw new Database_Exception($error);
		}

		return true;
	}

	public static function make_mysql_conect_command($with_dbname = false, $dbname = null)
	{
		$host = self::get_conection_configs('host');
		$port = self::get_conection_configs('port');
		$username = self::get_conection_configs('username');
		$password = self::get_conection_configs('password');

		if (!$username) throw new FuelException('Username is not set at configs.');

		$command = sprintf('mysql -u%s', $username);
		if (!empty($password)) $command .= sprintf(' -p%s', $password);
		if (!empty($hostname)) $command .= sprintf(' -h%s', $hostname);
		if (!empty($port)) $command .= sprintf(' -P%s', $port);

		if ($with_dbname)
		{
			$database = $dbname ?: self::get_conection_configs('database');
			if (!$database) throw new FuelException('Database is not set at configs.');
			$command .= sprintf(' %s', $database);
		}

		return $command;
	}
}
