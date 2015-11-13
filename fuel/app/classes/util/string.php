<?php
class Util_String
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

	/**
	 * @param int $digits 桁数
	 * @return string
	 */
	public static function get_random_code($digits = 4)
	{
		$code = '';
		for ($i = 0; $i < $digits; $i++)
		{
			$code .= mt_rand(0, 9);
		}

		return $code;
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
		$encoding or $encoding = \Fuel::$encoding;

		$pos = 0;
		$offset = 0;
		$len = mb_strlen($needle, $encoding);
		while ($n-- > 0 && ($pos = mb_strpos($str, $needle, $offset, $encoding)) !== false) {
			$offset = $pos + $len;
		}

		return $pos;
	}

	public static function truncate($body, $limit, $trimmarker = '...', $is_html = true, $is_special_chars = false)
	{
		$before_count = mb_strlen($body);

		$truncater = new Util_StrTruncater(array(
			'truncated_marker' => $trimmarker,
			'is_html' => $is_html,
			'is_special_chars' => $is_special_chars,
		));
		$body = $truncater->execute($body, $limit);
		$is_truncated = mb_strlen($body) < $before_count;

		return array($body, $is_truncated);
	}

	public static function truncate4line($body, $line, $trimmarker = '...', $is_rtrim = true, $encoding = null)
	{
		$encoding or $encoding = \Fuel::$encoding;
		$is_truncated = false;

		if (!$line) return array($body, $is_truncated);
		if (!$pos = Util_string::mb_strpos_n($body, "\n", $line, $encoding)) return array($body, $is_truncated);

		$is_truncated = $pos < mb_strlen($body, $encoding);
		$body = mb_substr($body, 0, $pos, $encoding);
		if ($is_truncated)
		{
			if ($is_rtrim) $body = rtrim($body);
			if ($trimmarker)
			{
				if (!Str::ends_with($body, "\n")) $body .= ' ';
				$body .= $trimmarker;
			}
		}

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

	public static function normalize_platform_dependent_chars($value, $is_use_normalizer = false)
	{
		$value = self::normalize_platform_dependent_chars_simple($value);
		if (!$is_use_normalizer) return $value;

		require_once APPPATH.'vendor'.DS.'PEAR'.DS.'I18N_UnicodeNormalizer'.DS.'UnicodeNormalizer.php';
		$normalizer = new I18N_UnicodeNormalizer();

		return $normalizer->normalize($value, 'NFKC');
	}

	public static function normalize_platform_dependent_chars_simple($value)
	{
		$arr = array(
			'①' => '(1)',
			'②' => '(2)',
			'③' => '(3)',
			'④' => '(4)',
			'⑤' => '(5)',
			'⑥' => '(6)',
			'⑦' => '(7)',
			'⑧' => '(8)',
			'⑨' => '(9)',
			'⑩' => '(10)',
			'⑪' => '(11)',
			'⑫' => '(12)',
			'⑬' => '(13)',
			'⑭' => '(14)',
			'⑮' => '(15)',
			'⑯' => '(16)',
			'⑰' => '(17)',
			'⑱' => '(18)',
			'⑲' => '(19)',
			'⑳' => '(20)',
			'Ⅰ' => 'I',
			'Ⅱ' => 'II',
			'Ⅲ' => 'III',
			'Ⅳ' => 'IV',
			'Ⅴ' => 'V',
			'Ⅵ' => 'VI',
			'Ⅶ' => 'VII',
			'Ⅷ' => 'VIII',
			'Ⅸ' => 'IX',
			'Ⅹ' => 'X',
			'ⅰ' => 'i',
			'ⅱ' => 'ii',
			'ⅲ' => 'iii',
			'ⅳ' => 'iv',
			'ⅴ' => 'v',
			'ⅵ' => 'vi',
			'ⅶ' => 'vii',
			'ⅷ' => 'viii',
			'ⅸ' => 'ix',
			'ⅹ' => 'x',
			'㊤' => '(上)',
			'㊥' => '(中)',
			'㊦' => '(下)',
			'㊧' => '(左)',
			'㊨' => '(右)',
			'㍉' => 'ミリ',
			'㍍' => 'メートル',
			'㌔' => 'キロ',
			'㌘' => 'グラム',
			'㌕' => 'キログラム',
			'㌧' => 'トン',
			'㌦' => 'ドル',
			'㍑' => 'リットル',
			'㌫' => 'パーセント',
			'㌢' => 'センチ',
			'㎝' => 'cm',
			'㎏' => 'kg',
			'㎥' => 'm2',
			'㏍' => 'K.K.',
			'℡' => 'TEL',
			'№' => 'No.',
			'㍻' => '平成',
			'㍼' => '昭和',
			'㍽' => '大正',
			'㍾' => '明治',
			'㈱' => '(株)',
			'㈲' => '(有)',
			'㈹' => '(代)',
		);

		return str_replace(array_keys($arr), array_values($arr), $value);
	}

	public static function convert2accepted_charas4cache_id($str, $replaced_char = '-', $is_accepted_dash = false)
	{
		$accepted_chars_range_pattern = static::replace2only_accepted_chars($str, 'a-zA-Z0-9_\-');
		if ($is_accepted_dash) $accepted_chars_range_pattern .= '.';

		return $accepted_chars_range_pattern;
	}

	public static function replace2only_accepted_chars($str, $accepted_chars_range_pattern, $replaced_char = '-')
	{
		return preg_replace('/([^'.$accepted_chars_range_pattern.']{1})/u', $replaced_char, $str);
	}

	public static function check_and_add_delimitter($str, $delimitter = '/')
	{
		return (substr($str, -1) == $delimitter) ? $str : $str.$delimitter;
	}

	public static function camelcase2ceparated($str, $is_convert_first_char_to_upper_case = false)
	{
		$str = str_replace('_', ' ', Inflector::underscore($str));
		if ($is_convert_first_char_to_upper_case) $str = ucfirst($str);

		return $str;
	}
}
