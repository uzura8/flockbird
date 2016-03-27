<?php

class Inflector extends Fuel\Core\Inflector
{
	public static function camelize($underscored_word, $is_lower = false)
	{
		$camelized = parent::camelize($underscored_word);
		if ($is_lower) $camelized = lcfirst($camelized);

		return $camelized;
	}
}

