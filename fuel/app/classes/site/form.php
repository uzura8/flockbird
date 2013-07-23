<?php
class Site_Form
{
	public static function get_public_flag_options()
	{
		$options = array();
		$public_flags = Site_Util::get_public_flags();
		foreach ($public_flags as $public_flag)
		{
			$options[$public_flag] = Config::get('term.public_flag.options.'.$public_flag);
		}

		return $options;
	}

	public static function get_public_flag_configs()
	{
		return array(
			'type'    => 'radio',
			'label'   => Config::get('term.public_flag.label'),
			'options' => self::get_public_flag_options(),
			'value'   => Config::get('site.public_flag.default'),
		);
	}
}
