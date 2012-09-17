<?php
class Util_security
{
	public static function get_csrf()
	{
		return md5(session_id());
	}

	public static function check_csrf_token($value = null)
	{
		$value = $value ?: \Input::post(\Config::get('security.csrf_token_key'), 'fail');

		return $value === self::get_csrf();
	}

	public static function check_csrf($value = null, $is_output_log = true)
	{
		if ( ! self::check_csrf_token($value))
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
