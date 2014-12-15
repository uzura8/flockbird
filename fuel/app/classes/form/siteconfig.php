<?php

class Form_SiteConfig
{
	public static function get_values($name, $default_value = null)
	{
		return conf($name, 'site', $default_value, '_');
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

	public static function get_validation($prefix)
	{
		$parts = explode('_', $prefix);
		$class = self::get_class_name((count($parts) > 1) ? array_shift($parts) : '');
		$method = 'get_validation_'.implode('_', $parts);

		return $class::$method();
	}

	protected static function get_class_name($prefix = '')
	{
		if (!$prefix) return __CLASS__;

		return sprintf('%s_%s', __CLASS__, Inflector::classify($prefix));
	}

	public static function save(\Validation $val, $posted_values)
	{
		$field_names = \Form_Util::get_field_names($val);
		foreach ($field_names as $name)
		{
			$site_config_obj = \Model_SiteConfig::get4name($name);
			if ($site_config_obj && $site_config_obj->value == $posted_values[$name]) continue;
			if (!$site_config_obj) $site_config_obj = \Model_SiteConfig::forge();

			$site_config_obj->name  = $name;
			$site_config_obj->value = $posted_values[$name];
			$site_config_obj->save();
		}
	}
}
