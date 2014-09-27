<?php
class Util_Develop
{
	public static function sleep($execute_absolute = false, $sleep_time = null)
	{
		if (!Config::get('develop.unitTest.strictDatetimeCheck.isEnabled') && !$execute_absolute) return;
		if (!$sleep_time) $sleep_time = Config::get('develop.unitTest.strictDatetimeCheck.sleepTime');

		sleep($sleep_time);
	}
}
