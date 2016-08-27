<?php

class Util_Lang
{
	public static function get_client_accept_lang($is_all_list = false)
	{
		if (!$accept_lang_str  = Input::server('HTTP_ACCEPT_LANGUAGE', '')) return '';
		if (!$accept_lang_list = explode(',', $accept_lang_str)) return '';

		$accept_langs = array();
		foreach ($accept_lang_list as $lang_str)
		{
			$langs = explode(';', $lang_str);
			$accept_langs[] = trim($langs[0]);
		}

		return $is_all_list ? $accept_langs : $accept_langs[0];
	}
}

