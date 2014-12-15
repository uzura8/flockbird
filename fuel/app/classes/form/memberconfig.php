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
}
