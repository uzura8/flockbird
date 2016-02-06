<?php
class Util_Array
{
	public static function array_in_array($targets, $haystacks)
	{
		if (!is_array($targets)) $targets = (array)$targets;
		foreach ($targets as $target)
		{
			if (!in_array($target, $haystacks)) return false;
		}

		return true;
	}

	public static function delete_in_array($targets, $search_list)
	{
		if (!is_array($targets)) $targets = (array)$targets;
		if (!is_array($search_list)) $search_list = (array)$search_list;

		if (!$search_list) return $targets;

		$return = array();
		foreach ($targets as $target)
		{
			if (in_array($target, $search_list)) continue;
			$return[] = $target;
		}

		return $return;
	}

	public static function get_neighborings($item, $list)
	{
		$before = null;
		$after  = null;
		$is_hit = false;
		foreach ($list as $value)
		{
			if ($is_hit)
			{
				$after = $value;
				break;
			}
			if ($value == $item) $is_hit = true;
			if (!$is_hit) $before = $value;
		}

		return array($before, $after);
	}

	public static function cast_values(array $values, $type, $is_check_empty = false)
	{
		switch ($type)
		{
			case 'int':
				$func = 'intval';
				break;
			case 'string':
				$func = 'strval';
				break;
			default :
				throw new \InvalidArgumentException("Second parameter must be 'int' or 'string'.");
				break;
		}

		$return = array();
		foreach ($values as $value)
		{
			if ($is_check_empty && empty($value)) return false;
			$return[] = $func($value);
		}

		return $return;
	}

	public static function trim_values(array $values, $character_mask = null)
	{
		return static::exec_func4array($values, 'trim', $character_mask);
	}

	public static function exec_func4array(array $values, $func, $option_args = array(), $is_check_empty = false)
	{
		if (!is_callable($func)) throw new InvalidArgumentException('Second parameter is invalid.');
		if (!is_array($option_args)) $option_args = (array)$option_args;

		$returns = array();
		foreach ($values as $value)
		{
			if ($is_check_empty && empty($value)) return false;

			array_unshift($option_args, $value);
			$returns[] = call_user_func_array($func, $option_args);
		}

		return $returns;
	}

	public static function convert_for_callback(array $array)
	{
		$return = array();
		foreach ($array as $key => $values)
		{
			if (is_string($key))
			{
				$each = array($key);
				$max = count($values);
				for ($i = 0; $i < $max; $i++) $each[] = $values[$i];
				$return[] = $each;
			}
			else
			{
				$return[] = $values;
			}
		}

		return $return;
	}

	public static function conv_arrays2str($list, $delimitter = ' ')
	{
		if (!is_array($list)) $list = (array)$list;
		foreach ($list as $key => $values)
		{
			if (!is_array($values)) continue;
			$list[$key] = implode($delimitter, $values);
		}

		return $list;
	}

	public static function conv_array2attr_string($attrs)
	{
		if (!$attrs) return '';

		if (!is_array($attrs)) $attrs = (array)$attrs;
		$list = array();
		foreach ($attrs as $key => $value)
		{
			if (is_array($value))
			{
				$list[] = sprintf("%s='%s'", $key, json_encode($value));
				continue;
			}

			$list[] = sprintf('%s="%s"', $key, $value);
		}

		return implode(' ', $list);
	}

	public static function get_first_key(array $array)
	{
		$row = each($array);

		return $row['key'];
	}

	public static function get_first(array $array)
	{
		if (count($array) < 1) return false;

		return array_shift($array);
	}

	public static function get_last_key(array $array)
	{
		$keys = array_keys($array);

		return end($keys);
	}

	public static function get_last(array $array)
	{
		if (count($array) < 1) return false;

		return array_pop($array);
	}

	public static function unset_item($target_value, array $array)
	{
		$target_key = array_search($target_value, $array);
		if ($target_key !== false) unset($array[$target_key]);

		 return $array;
	}

	public static function rand_weighted(array $array)
	{
		$sum  = array_sum($array);
		$rand = rand(1, $sum);
		foreach($array as $key => $weight)
		{
			if (($sum -= $weight) < $rand) return $key;
		}
	}

	public static function conv_arrays2key_value_str($list, $delimitter = null)
	{
		if (is_null($delimitter)) $delimitter = PHP_EOL;
		if (!is_array($list)) $list = (array)$list;

		$returns =  array();
		foreach ($list as $key => $value)
		{
			$returns[] = is_string($key) ? sprintf('%s: %s', $key, $value) : $value;
		}

		return implode($delimitter, $returns);
	}

	public static function set_key_from_value(array $array)
	{
		$returns = array();
		foreach ($array as $value) $returns[$value] = $value;

		return $returns;
	}

	public static function sort_to_top(array $list, $target, $is_check_target_included = false)
	{
		if (!$list) return $list;

		$list_before = array();
		$list_after = array();
		$is_finded = false;
		foreach ($list as $key => $value)
		{
			if (!$is_finded && $value == $target) $is_finded = true;
			if ($is_finded)
			{
				$list_before[$key] = $value;
			}
			else
			{
				$list_after[$key] = $value;
			}
			unset($list[$key]);
		}
		if (!$is_finded && $is_check_target_included) return false;

		return array_merge($list_before, $list_after);
	}
}
