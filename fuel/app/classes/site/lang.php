<?php

class Site_Lang
{
	public static function get_lang($is_check_member_lang_setting = true)
	{
		$default_lang = get_default_lang();

		if (!is_enabled_i18n()) return $default_lang;
		if (is_admin()) return $default_lang;

		// Member setting
		if ($lang = Session::get('lang')) return $lang;
		if ($is_check_member_lang_setting && $member_id = get_uid())
		{
			if ($lang = static::get_member_set_lang($member_id))
			{
				Session::set('lang', $lang);
				return $lang;
			}
		}

		// Client browser setting
		if ($lang = Site_Lang::get_client_lang()) return $lang;

		return $default_lang;
	}

	public static function configure_lang($is_check_member_lang_setting = true)
	{
		Lang::set_lang(static::get_lang($is_check_member_lang_setting), true);
		static::load_lang_files();
		static::load_configs_related_lang($is_check_member_lang_setting);
	}

	public static function reset_lang($lang)
	{
		Session::set('lang', $lang);
		Lang::set_lang($lang, true);
		static::load_lang_files();
		static::load_configs_related_lang();
	}

	protected static function load_lang_files()
	{
		if ($lang_files = Config::get('i18n.lang.files'))
		{
			foreach ($lang_files as $lang_file) Lang::load($lang_file, null, null, true, true);
		}
	}

	protected static function load_configs_related_lang($is_check_member_lang_setting = true)
	{
		Config::load(Site_Util::get_term_file_name($is_check_member_lang_setting), 'term');
		Config::load('navigation', 'navigation');
	}

	public static function get_member_set_lang($member_id)
	{
		if (! $lang = Model_MemberConfig::get_value($member_id, 'lang')) return false;

		return $lang;
	}

	public static function get_client_lang()
	{
		$accepteds = array_keys(conf('lang.options', 'i18n'));
		if (! $set_langs = Util_Lang::get_client_accept_lang(true)) return false;
		foreach ($set_langs as $lang)
		{
			if (in_array($lang, $accepteds)) return  $lang;
		}

		return false;
	}
}

