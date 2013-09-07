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

	public static function cast_bool_int($val)
	{
		if (empty($val)) return 0;

		return 1;
	}

	public static function check_uri_for_redilrect($target_uri, $domain = '')
	{
		// URLとして許可されている文字以外は許可しない
		if (!mb_ereg('\A[-_.!~*\'();\/?:@&=+\$,%#a-zA-Z0-9]*\z', $target_uri)) return false;

		// ドメイン未指定時に外部のドメインの指定は許可しない
		if (!$domain && preg_match('`^(https?:)?//`', $target_uri)) return false;

		// ドメイン指定時に指定したドメイン以外は許可しない
		if ($domain && !preg_match('`^(https?:)?//'.str_replace('.', '\.', $domain).'`', $target_uri)) return false;

		return true;
	}
}
