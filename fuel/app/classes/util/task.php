<?php

class Util_Task
{
	public static function output_result_message($result, $task_name, $success_message = null)
	{
		if ($success_message && $result === true)
		{
			$message = $success_message;
		}
		else
		{
			$message = sprintf('%s %s.', ucwords($task_name), static::get_result_message($result));
		}

		return static::output_message($message, $result);
	}

	public static function output_message($message, $message_level = true)
	{
		$color = static::get_message_color($message_level);

		return $color ? Cli::color($message, $color) : $message;
	}

	public static function get_result_message($message_level)
	{
		if ($message_level === true)
		{
			return 'success';
		}
		elseif ($message_level)
		{
			return $message_level;
		}

		return 'failed';
	}

	public static function get_message_color($message_level)
	{
		if ($message_level === 'success' || $message_level === true)
		{
			return '';
		}
		elseif ($message_level === 'info')
		{
			return '';
		}
		elseif ($message_level === 'warning')
		{
			return 'yellow';
		}
		elseif ($message_level === 'error' || !$message_level)
		{
			return 'red';
		}

		return '';
	}
}
