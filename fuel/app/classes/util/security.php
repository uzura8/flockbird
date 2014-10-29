<?php
class Util_security
{
	public static function get_csrf()
	{
		return md5(session_id());
	}

	public static function check_csrf_token($value = null)
	{
		$value = $value ?: \Input::param(\Config::get('security.csrf_token_key'), 'fail');

		return $value === self::get_csrf();
	}

	public static function check_csrf($value = null, $is_output_log = true)
	{
		if ( ! self::check_csrf_token($value))
		{
			if ($is_output_log) Util_Toolkit::log_error('CSRF');
			throw new HttpInvalidInputException('Invalid input data');
		}
	}

	public static function check_method($method, $is_output_log = true)
	{
		if (Input::method() != $method)
		{
			if ($is_output_log) Util_Toolkit::log_error('METHOD');
			throw new HttpMethodNotAllowed('Method not allowed');
		}
	}
}
