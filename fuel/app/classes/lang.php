<?php

class Lang extends Fuel\Core\Lang
{
	public static function get_all($language = '')
	{
		if ($language) return isset(static::$lines[$language]) ? static::$lines[$language] : array();

		return static::$lines;
	}
}
