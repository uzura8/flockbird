<?php
class Util_security
{
	public static function check_csrf($value = null)
	{
		if ( ! \Security::check_token($value))
		{
			\Log::error(
				'CSRF: '.
				\Input::uri().' '.
				\Input::ip().
				' "'.\Input::user_agent().'"'
			);
			throw new HttpInvalidInputException('Invalid input data');
		}
	}
}
