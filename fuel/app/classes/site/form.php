<?php
class Site_Form
{
	public static function get_public_flag_options($key = null)
	{
		$options = array();
		$public_flags = Site_Util::get_public_flags();
		foreach ($public_flags as $public_flag)
		{
			$options[$public_flag] = Config::get('term.public_flag.options.'.$public_flag);
		}

		if (isset($key)) return $options[$key];

		return $options;
	}

	public static function get_public_flag_configs($is_select = false)
	{
		return array(
			'type'    => $is_select ? 'select' : 'radio',
			'label'   => Config::get('term.public_flag.label'),
			'options' => self::get_public_flag_options(),
			'value'   => Config::get('site.public_flag.default'),
		);
	}
}
