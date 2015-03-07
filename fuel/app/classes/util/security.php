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

	public static function check_method($accept_methods, $is_output_log = true)
	{
		if (!$accept_methods) return true;

		if (!is_array($accept_methods)) $accept_methods = (array)$accept_methods;
		$accept_methods = array_map('strtoupper', $accept_methods);
		if (!in_array(Input::method(), $accept_methods))
		{
			if ($is_output_log) Util_Toolkit::log_error('METHOD');
			throw new HttpMethodNotAllowed('Method not allowed');
		}
	}
}
