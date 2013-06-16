<?php
class Util_string
{
	public static function camelize($underScoredWord)
	{
		$words = explode('_', $underScoredWord);

		$result = '';
		foreach ($words as $word)
		{
			$result .= ucfirst($word);
		}

		return $result;
	}

	public static function get_random($prefix = '', $surfix = '', $is_seccure = true)
	{
		$str = $prefix.rand(11111, 99999).$surfix;
		return ($is_seccure)? sha1($str) : md5($str);;
	}

	public static function get_unique_id()
	{
		return sha1(uniqid(mt_rand(), true));
	}

	public static function get_exploded($string, $number = 0, $delimitter = '_')
	{
		$parts = explode($delimitter, $string);
		if (count($parts) < $number + 1) return false;

		return $parts[$number];
	}

	public static function get_exploded_last($string, $delimitter = '_')
	{
		$parts = explode($delimitter, $string);

		return  array_pop($parts);
	}

	public static function convert2bytes($val)
	{
		$val  = trim($val);
		$last = strtolower($val[strlen($val) - 1]);
		switch($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	public static function check_int($target, $is_accept_string = true)
	{
		if (is_int($target)) return true;
		if (intval($target) > 0) return true;

		return false;
	}
}
