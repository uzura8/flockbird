<?php
class Site_Form
{
	public static function get_public_flag_options($key = null)
	{
		$options = array();
		$public_flags = Site_Util::get_public_flags();
		foreach ($public_flags as $public_flag)
		{
			$options[$public_flag] = term('public_flag.options.'.$public_flag);
		}

		if (isset($key)) return $options[$key];

		return $options;
	}

	public static function get_public_flag_configs($is_select = false)
	{
		return array(
			'type'    => $is_select ? 'select' : 'radio',
			'label'   => term('public_flag.label'),
			'options' => self::get_public_flag_options(),
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
}
