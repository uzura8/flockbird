<?php
class Site_Form
{
	public static function get_public_flag_options($key = null, $type = 'default', $with_no_change_option = false)
	{
		$options = array();
		if ($with_no_change_option) $options[99] = '変更しない';
		$public_flags = Site_Util::get_public_flags($type);
		foreach ($public_flags as $public_flag)
		{
			$options[$public_flag] = term('public_flag.options.'.$public_flag);
		}

		if (isset($key)) return $options[$key];

		return $options;
	}

	public static function get_public_flag_configs($is_select = false, $type = 'default')
	{
		return array(
			'type'    => $is_select ? 'select' : 'radio',
			'label'   => term('public_flag.label'),
			'options' => self::get_public_flag_options(null, $type),
			'value'   => conf('public_flag.default'),
		);
	}

	public static function get_form_options4config($config_key, $selected_key = null, $is_return_false_not_set_key = false)
	{
		if (!$options = Config::get($config_key)) throw new InvalidArgumentException('First parameter is invalid.');

		if (!is_null($selected_key) && isset($options[$selected_key])) return $options[$selected_key];
		if ($is_return_false_not_set_key) return false;

		return $options;
	}

	public static function get_field_id($name)
	{
		return sprintf('form_%s', str_replace('[]', '', $name));
	}
}
