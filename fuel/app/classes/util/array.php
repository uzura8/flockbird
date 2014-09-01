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

	public static function get_last(array $array)
	{
		if (count($array) < 1) return false;

		return array_pop($array);
	}
}
