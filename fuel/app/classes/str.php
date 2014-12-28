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
		$cut_length = $limit;
		$tags = array();
		if ($is_html)
		{
			$check_length = $limit;

			// Handle all the html tags.
			preg_match_all('/(<[^>]+>)([^<]*)/', $string, $matches, PREG_SET_ORDER);
			if (!$matches)
			{
				$offset_sc = 0;
				preg_match_all('/&[a-z]+;/i', $string, $sc_matches, PREG_SET_ORDER);
				foreach ($sc_matches as $sc_match)
				{
					$pos = static::pos($string, $sc_match[0], $offset_sc);
					$advance_pos = $pos - $offset_sc;
					if ($check_length <= $advance_pos)
					{
						$cut_length = $offset_sc + $check_length;
						break;
					}
					$check_length -= $advance_pos;
					$offset_sc = $pos;
					$sc_str_length = static::length($sc_match[0]);
					if ($check_length == 1)
					{
						$cut_length = $offset_sc + $sc_str_length;
						break;
					}

					$check_length--;
					$offset_sc += $sc_str_length;
					$cut_length += ($sc_str_length - 1);
				}
			}
			else
			{
				$single_tags = array('br', 'img', 'hr');
				$offset = 0;
				foreach ($matches as $match)
				{
					if (!$offset)
					{
						$offset = static::pos($string, $match[0]);

						$sc_check_target_str = static::sub($string, 0, $offset);
						preg_match_all('/&[a-z]+;/i', $sc_check_target_str, $sc_matches, PREG_SET_ORDER);
						if ($sc_matches)
						{
							$offset_sc = 0;
							foreach ($sc_matches as $sc_match)
							{
								$pos = static::pos($sc_check_target_str, $sc_match[0], $offset_sc);
								$advance_pos = $pos - $offset_sc;
								if ($check_length <= $advance_pos)
								{
									$cut_length = $offset_sc + $check_length;
									break 2;
								}
								$check_length -= $advance_pos;
								$offset_sc = $pos;
								$sc_str_length = static::length($sc_match[0]);
								if ($check_length == 1)
								{
									$cut_length = $offset_sc + $sc_str_length;
									break 2;
								}

								$check_length--;
								$offset_sc += $sc_str_length;
								$cut_length += ($sc_str_length - 1);
							}
						}
						else
						{
							if ($check_length <= $offset)
							{
								$cut_length = $check_length;
								break;
							}
							$check_length -= $offset;
						}
					}

					// handle tag.
					$tag = static::sub(strtok($match[0], " \t\n\r\0\x0B>"), 1);
					if($tag[0] != '/' && !in_array($tag, $single_tags))
					{
						$tags[] = $tag;
					}
					elseif (end($tags) == static::sub($tag, 1))
					{
						array_pop($tags);
					}


					$not_tag_str_length = static::length($match[2]);

					$sc_check_target_str = $match[2];
					preg_match_all('/&[a-z]+;/i', $sc_check_target_str, $sc_matches, PREG_SET_ORDER);
					if ($sc_matches)
					{
						$offset_sc = 0;
						foreach ($sc_matches as $sc_match)
						{
							$pos = static::pos($sc_check_target_str, $sc_match[0], $offset_sc);
							$advance_pos = $pos - $offset_sc;
							if ($check_length <= $advance_pos)
							{
								$cut_length = $offset + static::length($match[1]) + $offset_sc + $check_length;
								break 2;
							}
							$check_length -= $advance_pos;
							$offset_sc = $pos;
							$sc_str_length = static::length($sc_match[0]);
							if ($check_length == 1)
							{
								$cut_length = $offset + static::length($match[1]) + $offset_sc + $sc_str_length;
								break 2;
							}

							$check_length--;
							$offset_sc += $sc_str_length;
							$cut_length += ($sc_str_length - 1);
						}
						$rest_length = $not_tag_str_length - $offset_sc;
						if ($check_length <= $rest_length)
						{
							$cut_length = $offset + static::length($match[1]) + $offset_sc + $check_length;
						}
						$check_length -= $rest_length;
					}
					else
					{
						if ($not_tag_str_length >= $check_length)
						{	
							$cut_length = $offset + static::length($match[1]) + $check_length;
							break;
						}
						$check_length -= $not_tag_str_length;
					}
					$cut_length += static::length($match[1]);
					$offset += static::length($match[0]);
				}
			}
		}
		$new_string = static::sub($string, 0, $cut_length);
		$new_string .= (count($tags = array_reverse($tags)) ? '</'.implode('></',$tags).'>' : '');
		$new_string .= (static::length($string) > $cut_length ? $continuation : '');

		return $new_string;
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
