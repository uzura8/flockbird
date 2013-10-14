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
}
