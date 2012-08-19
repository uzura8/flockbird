<?php
class Util_admin
{
	public static function check_is_admin_request()
	{
		if (Uri::segment(1) == 'admin') return true;

		return false;
	}
}
