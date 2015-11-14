<?php

class Site_Task
{
	public static function check_is_running($task_name, $enabled_priod = null)
	{
		$site_config_name = self::get_site_config_name($task_name);
		if (!$site_config = Model_SiteConfig::get4name($site_config_name)) return false;

		if (!$enabled_priod) $enabled_priod = conf(sprintf('%s.runningFlag.enabledPriod', $task_name), 'task');
		if (!$enabled_priod) $enabled_priod = conf('default.runningFlag.enabledPriod', 'task');
		if ($enabled_priod)
		{
			if (Util_Date::check_is_passed($site_config->updated_at, $enabled_priod, null, false))
			{
				$site_config->value = 0;
				$site_config->save();
			}
		}

		return (bool)$site_config->value;
	}

	public static function update_running_flag($task_name, $is_start = true)
	{
		if ($is_start && static::check_is_running($task_name))
		{
			throw new TaskAlreadyRunningException('Another task is running.');
		}

		$site_config_name = self::get_site_config_name($task_name);
		if (!$site_config = Model_SiteConfig::get4name($site_config_name))
		{
			$site_config = Model_SiteConfig::forge();
			$site_config->name = $site_config_name;
		}
		$site_config->value = $is_start ? 1 : 0;

		return $site_config->save();
	}

	protected static function get_site_config_name($task_name)
	{
		return sprintf('task_running_%s', $task_name);
	}

	public static function output_result_message($result, $task_name, $message = null, $is_output_log = false)
	{
		$task_name_formatted = ucwords($task_name);
		if (!$message) $message = static::get_message4result($result);
		$message = sprintf('%s: %s', $task_name_formatted, $message);

		if ($is_output_log) \Util_Toolkit::log_error($message, static::get_error_level4log($result), true, true);

		return Util_Task::output_message($message, $result);
	}

	public static function get_message4result($result)
	{
		if ($result === true) return 'successed';
		if ($result === false) return 'failed';

		switch ($result)
		{
			case 'warning':
			case 'error':
				return 'failed';
		}

		return 'completed';
	}

	public static function get_error_level4log($result)
	{
		if (in_array($result, array('error', 'warning', 'info', 'debug'), true)) return $result;

		return 'info';
	}
}
