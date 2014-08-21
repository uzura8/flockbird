<?php

class Util_Task
{
	public static function output_result_message($result, $task_name, $success_message = null)
	{
		if ($success_message && $result)
		{
			$message = $success_message;
		}
		else
		{
			$message = sprintf('%s %s.', ucwords($task_name), $result ? 'success' : 'failed');
		}

		return static::output_message($message, $result);
	}

	public static function output_message($message, $is_success = true)
	{
		return $is_success ? $message : Cli::color($message, 'red');
	}
}
