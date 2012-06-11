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
}
