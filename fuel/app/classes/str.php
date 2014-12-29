<?php

class Str extends Fuel\Core\Str
{
	/**
	 * Truncates a string to the given length.  It will optionally preserve
	 * HTML tags if $is_html is set to true.
	 *
	 * @param   string  $string        the string to truncate
	 * @param   int     $limit         the number of characters to truncate too
	 * @param   string  $continuation  the string to use to denote it was truncated
	 * @param   bool    $is_html       whether the string has HTML
	 * @return  string  the truncated string
	 */
	public static function truncate($string, $limit, $continuation = '...', $is_html = false)
	{
		$truncater = new Util_StrTruncater(array(
			'truncated_marker' => $continuation,
			'is_html' => $is_html,
			'is_special_chars' => $is_html,
		));

		return $truncater->execute($string, $limit);
	}

	/**
	 * strpos
	 *
	 * @param   string  $str       required
	 * @param   string  $needle    required
	 * @param   int     $offset    default 0
	 * @param   string  $encoding  default utf-8
	 * @return  int
	 */
	public static function pos($str, $needle, $offset = 0, $encoding = null)
	{
		$encoding or $encoding = \Fuel::$encoding;

		return function_exists('mb_strpos')
			? mb_strpos($str, $needle, $offset, $encoding)
			: strpos($str, $needle, $offset);
	}
}
