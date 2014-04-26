<?php

class Uri extends Fuel\Core\Uri
{

	/**
	 * Returns the full uri with query as a string
	 *
	 * @return  string
	 */
	public static function string_with_query()
	{
		$return = static::string();
		if ($query = \Input::server('QUERY_STRING'))
		{
			$return .= '?'.$query;
		}

		return $return;
	}
}
