<?php
class Util_Date
{
	static function check_is_same_minute($time1, $time2)
	{
		if (!$minute1 = self::remove_second_string($time1)) return false;
		if (!$minute2 = self::remove_second_string($time2)) return false;

		return $minute1 == $minute2;
	}

	static function remove_second_string($datetime)
	{
		$pattern = '#^([12]{1}[0-9]{3}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2})(:[0-9]{2})?$#';
		if (!preg_match($pattern, $datetime, $matches)) return false;

		return $matches[1];
	}
}
