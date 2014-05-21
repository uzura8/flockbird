<?php
namespace Admin;

class Site_Util
{
	public static function get_navi($config_navi_key, $is_check_acl = true)
	{
		$navi_list = Config::get('navigation.admin.'.$config_navi_key);
		foreach ($navi_list as $name => $uri)
		{
			\Auth::has_access();
		}

		return $navi_list;
	}

	public static function check_exists_accessible_uri($paths)
	{
		if (! is_array($paths)) return \Auth::has_access(\Site_Util::get_acl_path($paths).'.GET');

		foreach ($paths as $name => $path)
		{
			if (\Auth::has_access(\Site_Util::get_acl_path($path).'.GET')) return true;
		}

		return false;
	}
}
