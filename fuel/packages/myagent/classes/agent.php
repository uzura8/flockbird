<?php
namespace MyAgent;

class Agent
{
	public static function is_mobile()
	{
		return static::mobile_detect_instance()->isMobile();
	}

	public static function is_tablet()
	{
		return static::mobile_detect_instance()->isTablet();
	}

	public static function is_mobile_device()
	{
		if (static::is_tablet()) return false;

		return static::is_mobile();
	}

	protected static function mobile_detect_instance()
	{
		require_once PKGPATH.'myagent'.DS.'vendor'.DS.'Mobile_Detect.php';
		$instance = new \Mobile_Detect();

		return $instance;
	}
}
