<?php

class Form_MemberConfig
{
	public static function get_default_values()
	{
		return conf('member_config_default');
	}

	public static function get_default_value($key, $default = null)
	{
		return conf('member_config_default.'.$key, null, $default);
	}

	public static function get_value($member_id, $name, $default_value = null)
	{
		$value = Model_MemberConfig::get_value($member_id, $name);

		return !is_null($value) ? $value : $default_value;
	}

	public static function get_names($prefixes)
	{
		if (!is_array($prefixes)) $prefixes = (array)$prefixes;
		$names = array();
		foreach ($prefixes as $prefix)
		{
			$names = array_merge($names, \Form_Util::get_field_names(self::get_validation($prefix)));
		}

		return $names;
	}

	public static function get_validation($member_id, $prefix, $namespace = null)
	{
		$parts = explode('_', $prefix);
		$class = self::get_class_name((count($parts) > 1) ? array_shift($parts) : '', $namespace);
		$method = 'get_validation';
		if ($parts) $method .= '_'.implode('_', $parts);

		return $class::$method($member_id);
	}

	protected static function get_class_name($prefix = '', $namespace = null)
	{
		$namespace_prefix = $namespace ? sprintf('\\%s\\', $namespace) : '';
		if (!$prefix) return $namespace_prefix.__CLASS__;

		return sprintf('%s%s_%s', $namespace_prefix, __CLASS__, Inflector::classify($prefix));
	}

	public static function save($member_id, Validation $val, $posted_values)
	{
		$field_names = Form_Util::get_field_names($val);
		foreach ($field_names as $name)
		{
			$member_config = Model_MemberConfig::get_one4member_id_and_name($member_id, $name);
			if ($member_config && $member_config->value == $posted_values[$name]) continue;
			if (!$member_config) $member_config = Model_MemberConfig::forge();

			$member_config->member_id  = $member_id;
			$member_config->name  = $name;
			$member_config->value = $posted_values[$name];
			$member_config->save();
		}
	}

	public static function get_validation_lang($member_id)
	{
		$val = \Validation::forge('member_config_lang');

		$name = 'lang';
		$value = self::get_lang_value($member_id);
		$options = self::get_lang_options();
		$val->add($name, term('site.lang', 'site.setting'), array('type' => 'select', 'options' => $options, 'value' => $value))
				->add_rule('required')
				->add_rule('in_array', array_keys($options));

		if (is_enabled_timezone())
		{
			$name = 'timezone';
			$value = self::get_timezone_value($member_id);
			$options = self::get_timezone_options();
			$val->add($name, term('site.timezone', 'site.setting'), array('type' => 'select', 'options' => $options, 'value' => $value))
					->add_rule('required')
					->add_rule('in_array', array_keys($options));
		}

		return $val;
	}

	public static function get_lang_options($value = null)
	{
		return conf('lang.options', 'i18n');
	}

	public static function get_lang_value_label($member_id)
	{
		if (! $lang = Model_MemberConfig::get_value($member_id, 'lang')) return '';

		return Arr::get(static::get_lang_options(), $lang, '');
	}

	public static function get_lang_value($member_id, $is_return_default_lang = true)
	{
		if ($lang = Model_MemberConfig::get_value($member_id, 'lang')) return $lang;

		$default_lang = get_default_lang();
		if (! $member = Model_Member::get_one4id($member_id)) return $is_return_default_lang ? $default_lang : '';
		if (empty($member->country)) return $is_return_default_lang ? $default_lang : '';
		if ($lang = conf('lang.countryLang.'.$member->country, 'i18n')) return $lang;

		return $is_return_default_lang ? $default_lang : '';
	}

	public static function get_timezone_options($value = null)
	{
		return conf('timezone.options', 'i18n');
	}

	public static function get_timezone_value_label($member_id)
	{
		if (! $timezone = Model_MemberConfig::get_value($member_id, 'timezone')) return '';

		return Arr::get(static::get_timezone_options(), $timezone, '');
	}

	public static function get_timezone_value($member_id, $is_return_default = true)
	{
		if ($timezone = Model_MemberConfig::get_value($member_id, 'timezone')) return $timezone;

		$default_timezone = Config::get('i18n.timezone.default', 'Asia/Tokyo');
		if (! $member = Model_Member::get_one4id($member_id)) return $is_return_default ? $default_timezone : '';
		if (empty($member->country)) return $is_return_default ? $default_timezone : '';
		if ($timezone = conf(sprintf('timezone.countryTimezone.%s.default', $member->country), 'i18n')) return $timezone;

		return $is_return_default ? $default_timezone : '';
	}
}
