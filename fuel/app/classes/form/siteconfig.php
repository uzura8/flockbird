<?php

class Form_SiteConfig
{
	public static function get_values($name, $default_value = null)
	{
		$saved_value = \Model_SiteConfig::get_value4name_as_assoc($name);
		if (false === $saved_value) return $default_value;

		return $saved_value;
	}

	public static function get_names($prefix = '')
	{
		return \Form_Util::get_field_names(self::get_validation($prefix));
	}

	public static function get_validation($prefix, $is_set_saved_value = false)
	{
		$parts = explode('_', $prefix);
		$class = self::get_class_name((count($parts) > 1) ? array_shift($parts) : '');
		$method = 'get_validation_'.implode('_', $parts);

		return $class::$method($is_set_saved_value);
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
