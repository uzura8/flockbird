<?php
class Util_security
{
	public static function check_csrf($value = null, $is_output_log = true)
	{
		if ( ! \Security::check_token($value))
		{
			if ($is_output_log)
			{
				\Log::error(
					'CSRF: '.
					\Input::uri().' '.
					\Input::ip().
					' "'.\Input::user_agent().'"'
				);
			}
			throw new HttpInvalidInputException('Invalid input data');
		}
	}

	public static function check_method($method, $is_output_log = true)
	{
		if (Input::method() != $method)
		{
			if ($is_output_log)
			{
				\Log::error(
					'METHOD: '.
					\Input::uri().' '.
					\Input::ip().' '.
					\Input::method().
					' "'.\Input::user_agent().'"'
				);
			}
			throw new HttpInvalidInputException('Invalid input data');
		}
	}
}
