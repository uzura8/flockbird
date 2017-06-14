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

	public static function get_country_name4code($code, $is_throw_exception = true)
	{
		$code = strtoupper($code);
		if (! $country = conf('country.options.'.$code, 'i18n'))
		{
			if ($is_throw_exception) throw new InvalidArgumentException('First parameter is invalid.');

			return '';
		}

		return $country;
	}

	public static function get_country_options($is_add_nodata = false)
	{
		if (! $is_add_nodata) return conf('country.options', 'i18n');

		return array('' => t('common.all')) + conf('country.options', 'i18n');
	}

	public static function set_locale($locale)
	{
		setlocale(LC_ALL, $locale);
	}

	public static function get_locale()
	{
		return setlocale(LC_ALL, 0);
	}
}

