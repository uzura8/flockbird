<?php

class Util_Date
{
	public static function check_is_same_minute($time1, $time2)
	{
		if (!$time1 || !$time2) return false;
		if (!$minute1 = self::remove_second_string($time1)) return false;
		if (!$minute2 = self::remove_second_string($time2)) return false;

		return $minute1 == $minute2;
	}

	public static function remove_second_string($datetime)
	{
		$pattern = '#^([12]{1}[0-9]{3}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2})(:[0-9]{2})?$#';
		if (!preg_match($pattern, $datetime, $matches)) return false;

		return $matches[1];
	}

	public static function check_is_past($val, $base = '', $min = '', $is_return_time = false, $is_return_time_format = 'Y-m-d H:i:s')
	{
		if (empty($val)) return false;
		if (!$time = strtotime($val)) return false;

		$base_time = empty($base) ? time() : strtotime($base);
		if ($time > $base_time) return false;

		$min_time = empty($min) ? strtotime('- 120 years') : strtotime($min);
		if ($time < $min_time) return false;

		if ($is_return_time) return date($is_return_time_format, $time);

		return true;
	}

	public static function check_is_future($val, $base = '', $max = '', $is_return_time = false, $is_return_time_format = 'Y-m-d H:i:s')
	{
		if (empty($val)) return false;
		if (!$time = strtotime($val)) return false;

		$base_time = empty($base) ? time() : strtotime($base);
		if ($time < $base_time) return false;

		$max_time = empty($max) ? strtotime('+ 50 years') : strtotime($max);
		if ($time > $max_time) return false;

		if ($is_return_time) return date($is_return_time_format, $time);

		return true;
	}

	public static function sprit_date_str($date, $is_return_assoc = false, $delimiter = '-')
	{
		$items = explode($delimiter, $date);
		$count = count($items);
		if ($count < 2 || $count > 3) throw new InvalidArgumentException('Date format is invalid.');

		if ($count == 3)
		{
			if (!$is_return_assoc) return array((int)$items[0], (int)$items[1], (int)$items[2]);

			return array(
				'year' => (int)$items[0],
				'month' => (int)$items[1],
				'date' => (int)$items[2],
			);
		}

		if (!$is_return_assoc) return array((int)$items[0], (int)$items[1]);

		return array(
			'month' => (int)$items[0],
			'date' => (int)$items[1],
		);
	}

	public static function combine_date_str($month, $date, $year = null, $delimiter = '-')
	{
		$date_str = sprintf('%02d%s%02d', $month, $delimiter, $date);
		if ($year) $date_str = sprintf('%04d%s', $year, $delimiter).$date_str;

		return $date_str;
	}

	public static function conv_date_format($date_str, $format = '%d/%d')
	{
		list($month, $date) = self::sprit_date_str($date_str);

		return sprintf($format, $month, $date);
	}

	public static function calc_age($year, $date_str = null)
	{
		if (!$date_str) $date_str = '1231';// 日付がない場合は最も遅い日付で年齢計算する
		if (strpos($date_str, '-') !== false) $date_str = str_replace('-', '', $date_str);
		$target_date = $year.$date_str;
		$now = date('Ymd');

		return floor(($now - $target_date) / 10000);
	}

	public static function get_datetime_list($base_datatime, $second = 1, $type = 'both')
	{
		if (!in_array($type, array('both', 'past', 'future'))) throw new InvalidArgumentException('Third parameter is invalid.');

		$base_time = strtotime($base_datatime);
		$return = array();
		switch ($type)
		{
			case 'future':
				$min = 0;
				$max = $second;
				break;
			case 'past':
				$min = 0 - $second;
				$max = 0;
				break;
			case 'both':
			default :
				$min = 0 - $second;
				$max = $second;
				break;
		}
		for ($i = $min; $i <= $max; $i++)
		{
			$return[] = \Date::forge($base_time + $i)->format('mysql');
		}

		return $return;
	}
}
