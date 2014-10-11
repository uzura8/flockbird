<?php
class Site_Develop
{
	public static function get_cache($cache_key, $cache_expir)
	{
		try
		{
			return \Cache::get($cache_key, $cache_expir);
		}
		catch (\CacheNotFoundException $e)
		{
			return null;
		}
	}
}
