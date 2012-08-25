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

	public static function get_random($prefix = '', $surfix = '')
	{
		return sha1($prefix.rand(11111, 99999).$surfix);
	}

	public static function get_unique_id()
	{
		return sha1(uniqid(mt_rand(), true));
	}
}
