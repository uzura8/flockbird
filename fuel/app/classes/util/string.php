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

	public static function get_exploded_first($string, $delimitter = '_')
	{
		$parts = explode($delimitter, $string);

		return  array_shift($parts);
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

	// n番目に文字列が現れる場所を探す
	public static function mb_strpos_n($str, $needle, $n = 0, $encoding = null)
	{
		$pos = 0;
		$offset = 0;
		if (!$encoding) $encoding = mb_internal_encoding();
		$len = mb_strlen($needle, $encoding);
		while ($n-- > 0 && ($pos = mb_strpos($str, $needle, $offset, $encoding)) !== false) {
			$offset = $pos + $len;
		}

		return $pos;
	}

	public static function truncate_lines($body, $line, $trimmarker = '...', $encoding = null)
	{
		$is_truncated = false;

		if (!$line) return array($body, $is_truncated);
		if (!$pos = Util_string::mb_strpos_n($body, "\n", $line, $encoding)) return array($body, $is_truncated);

		$is_truncated = $pos < mb_strlen($body, $encoding);
		$body = mb_substr($body, 0, $pos, $encoding);
		if ($is_truncated && $trimmarker) $body .= $trimmarker;

		return array($body, $is_truncated);
	}

	public static function combine_nums(array $nums, $is_sort = false, $delimitter = '_')
	{
		if (empty($nums)) throw new InvalidArgumentException('First parameter is empty array.');
		if (count($nums) == 1) return $num;

		if ($is_sort) sort($nums, SORT_NUMERIC);

		return implode($delimitter, $nums);
	}

	public static function split_str($str)
	{
		$array = array();
		for ($i=0; $i < strlen($str); $i++) {
			$c = $str[$i];
			array_push($array, $c);
		}

		return $array;
	}

	public static function get_next_alphabet($str = '')
	{
		if (!$str) return 'a';

		$num = ord($str);
		if ($num < 97 || $num > 122) return 'a';

		$num++;
		if ($num > 122) return 'aa';

		return chr($num);
	}

	public static function get_next_alpha_str($str = '')
	{
		if (!$str) return 'a';

		$items = self::split_str($str);
		$str = array_pop($items);
		$str = self::get_next_alphabet($str);
		$items[] = $str;

		return implode('', $items);
	}
}
