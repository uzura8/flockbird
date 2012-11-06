<?php
class Agent extends Fuel\Core\Agent
{
	public static function _init()
	{
		parent::_init();

		$sp_list = array(
			'iPhone',
			'iPod',
			'Android',
			'IEMobile',
			'dream',
			'CUPCAKE',
			'blackberry9500',
			'blackberry9530',
			'blackberry9520',
			'blackberry9550',
			'blackberry9800',
			'webOS',
			'incognito',
			'webmate'
		);

		$pattern = '/'.implode('|', $sp_list).'/i';
		static::$properties['x_issmartphone'] = preg_match($pattern, static::$user_agent) ? true : false;
	}

	public static function is_smartphone()
	{
		return static::$properties['x_issmartphone'];
	}
}
