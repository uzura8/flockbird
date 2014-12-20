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

	public static function check_legacy_ie($criteria_version = 8)
	{
		if (!$criteria_version) return false;

		if (!preg_match('/MSIE\s([\d.]+)/i', \Input::user_agent(), $matches)) return false;
		$version = floor($matches[1]);

		return $version <= $criteria_version;
	}
}
