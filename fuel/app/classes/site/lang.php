<?php

class Site_Lang
{
	public static function get_lang($is_check_member_lang = true, $is_check_session_lang = true)
	{
		$default_lang = static::get_default_lang();

		if (!is_enabled_i18n()) return $default_lang;
		if (is_admin()) return conf('adminDefaultLang', 'i18n');

		// Member setting
		if ($is_check_session_lang && $lang = static::get_session_lang()) return $lang;
		if ($is_check_member_lang  && $member_id = get_uid())
		{
			if ($lang = static::get_member_set_lang($member_id))
			{
				if ($is_check_session_lang) static::set_session('lang', $lang);
				return $lang;
			}
		}

		// Client browser setting
		if ($lang = Site_Lang::get_client_lang()) return $lang;

		return $default_lang;
	}

	public static function configure_lang($is_check_member_lang = true, $is_check_session_lang = true, $country = null)
	{
		$lang = static::get_lang($is_check_member_lang, $is_check_session_lang);
		static::reset_lang($lang, $is_check_session_lang, $country);
		if ($is_check_session_lang && $country) static::set_session('country', $country);
	}

	public static function reset_lang($lang, $is_set_session = true, $country = null)
	{
		if ($is_set_session) static::set_session('lang', $lang);
		Lang::set_lang($lang, true);
		static::load_lang_files();
		static::load_configs_related_lang($lang);
		if ($is_set_session) static::set_locale($lang, $country);
	}

	public static function set_locale($lang, $country = null)
	{
		$locale = static::get_locale($lang, $country);
		Util_Lang::set_locale($locale);
	}

	protected static function load_lang_files()
	{
		if ($lang_files = Config::get('i18n.lang.files'))
		{
			foreach ($lang_files as $lang_file) Lang::load($lang_file, null, null, true, true);
		}
	}

	protected static function load_configs_related_lang($lang)
	{
		Config::load(Site_Util::get_term_file_name($lang), 'term', true);
		Config::load(is_admin() ? 'admin::navigation' : 'navigation', 'navigation', true);
	}

	public static function get_client_lang($is_return_default_lang = false)
	{
		$accepteds = array_keys(conf('lang.options', 'i18n'));
		if (! $set_langs = Util_Lang::get_client_accept_lang(true))
		{
			return $is_return_default_lang ? static::get_default_lang() : false;
		}

		foreach ($set_langs as $lang)
		{
			if (in_array($lang, $accepteds)) return  $lang;
		}

		return $is_return_default_lang ? static::get_default_lang() : false;
	}

	public static function get_member_set_lang($member_id)
	{
		if (! $lang = Model_MemberConfig::get_value($member_id, 'lang')) return false;

		return $lang;
	}

	public static function get_locale($lang, $country = null)
	{
		$default_locale = static::get_default_locale($lang);
		if (! $country) return $default_locale;

		$locale = sprintf('%s_%s.UTF-8', $lang, $country);
		$accepted_locales = static::get_accepted_locales();
		if (in_array($locale, $accepted_locales)) return $locale;

		return $default_locale;
	}

	public static function get_date_format($suffix = null)
	{
		$accept_suffixes = array('full', 'short', 'named');
		if ($suffix && ! in_array($suffix, $accept_suffixes)) $suffix = null;

		$locale = Util_Lang::get_locale();
		$format = static::get_date_format4locale($locale);
		if ($suffix) $format .= '_'.$suffix;

		return $format;
	}

	public static function get_date_format4locale($locale)
	{
		$conf = Config::get('i18n.date');

		return Arr::get($conf, 'localeFormat.'.$locale, $conf['defaultFormat']);
	}

	public static function get_default_lang()
	{
		return Config::get('i18n.lang.default', 'ja');
	}

	public static function get_default_country()
	{
		return Config::get('i18n.country.default', 'ja');
	}

	public static function get_default_locale($lang)
	{
		$confs = Config::get('i18n.locale');
		if ($default4lang = Arr::get($confs, 'defaults.'.$lang)) return $default4lang;

		return Arr::get($confs, 'default', 'ja_JP');
	}

	public static function get_accepted_locales()
	{
		return Config::get('i18n.locale.options', array());
	}

	public static function set_session($key, $lang)
	{
		return Session::set('i18n.'.$key, $lang);
	}

	public static function get_session($key)
	{
		return Session::get('i18n.'.$key);
	}

	public static function get_session_lang()
	{
		return Session::get('i18n.lang');
	}

	public static function delete_session($key = null)
	{
		if (! $key) return Session::delete('i18n');

		return Session::delete('i18n.'.$key);
	}
}

