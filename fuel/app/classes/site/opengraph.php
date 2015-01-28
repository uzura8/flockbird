<?php
require_once APPPATH.'vendor'.DS.'opengraph'.DS.'OpenGraph.php';

class Site_OpenGraph
{
	public static function get_analized_data($url, $is_cached = false, $cache_key_prefix = '', $cache_expire = null)
	{
		if (is_null($cache_expire)) $cache_expire = 60 * 60 * 24;
		if (!$is_cached) return static::analyze_url($url);

		$device_type = \MyAgent\Agent::is_mobile_device() ? 'sp' : 'pc';
		$cache_key = static::get_cache_key($url, $device_type, $cache_key_prefix);
		try
		{
			$analized_data =  \Cache::get($cache_key, $cache_expire);
		}
		catch (\CacheNotFoundException $e)
		{
			$analized_data = static::analyze_url($url);
			\Cache::set($cache_key, $analized_data, $cache_expire);
		}

		return $analized_data;
	}

	public static function analyze_url($url)
	{
		$graph = OpenGraph::fetch($url);
		$keys = array('title', 'type', 'image', 'url', 'site_name', 'description');
		$returns = array();
		foreach ($keys as $key)
		{
			if (!isset($graph->{$key})) continue;
			$returns[$key] = $graph->{$key};
		}
		if ($returns && empty($returns['url'])) $returns['url'] = $url;

		return $returns;
	}

	protected static function get_cache_key($url, $device_type = 'pc', $prefix = '')
	{
		$target_str = Util_String::convert2accepted_charas4cache_id(preg_replace('#https?://#u', '', $url));

		return sprintf('%s%s_%s', $prefix, $target_str, $device_type);
	}
}
