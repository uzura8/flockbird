<?php
class Util_Array
{
	public static function array_in_array($targets, $haystacks)
	{
		foreach ($targets as $target)
		{
			if (!in_array($target, $haystacks)) return false;
		}

		return true;
	}
}
