<?php

class Site_Task
{
	public static function check_is_running($task_name)
	{
		$site_config_name = self::get_site_config_name($task_name);
		if (!$site_config = Model_SiteConfig::get4name($site_config_name)) return false;

		if ($enabled_priod = conf(sprintf('%s.runningFlag.enabledPriod', $task_name), 'task'))
		{
			if (Util_Date::check_is_passed($site_config->updated_at, $enabled_priod, null, $is_time_format = true))
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
}
