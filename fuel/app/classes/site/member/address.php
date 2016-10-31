<?php

class Site_Member_Address
{
	public static function get_full_name($first_name, $last_name, $is_lang_ja = false)
	{
		$items = array($first_name, $last_name);
		if ($is_lang_ja) $items = array_reverse($items);

		return trim(implode(' ', $items));
	}

	public static function get_address($addresses, $is_lang_ja = false)
	{
		$items = array();
		if ($addresses['address02']) $items[] = $addresses['address02'];
		if ($addresses['address01']) $items[] = $addresses['address01'];
		if ($addresses['region'])    $items[] = $addresses['region'];
		if ($addresses['postal_code']) $items[] = $addresses['postal_code'];
		if ($addresses['country'] && $country = Util_Lang::get_country_name4code($addresses['country']))
		{
			$items[] = $country;
		}
		if ($is_lang_ja) $items = array_reverse($items);

		return trim(implode(' ', $items));
	}
}

