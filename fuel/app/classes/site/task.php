<?php

class Site_Task
{
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
