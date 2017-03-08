<?php

class Site_Notification
{
	static $enable_types = array('notice', 'message');

	public static function get_unread_count($type, $member_id)
	{
		if (!static::check_enabled_type($type)) throw new InvalidArgumentException(__METHOD__.':First parameter is invalid.');

		if (!static::check_is_enabled_cahce($type))
		{
			return static::get_unread_count4member_id($type, $member_id);
		}

		return static::get_unread_count_cache($type, $member_id, true);
	}

	protected static function check_enabled_type($type)
	{
		return in_array($type, static::$enable_types);
	}

	public static function get_enabled_types()
	{
		return static::$enable_types;
	}

	public static function check_is_enabled_cahce($type, $is_check_module_enabled = false)
	{
		if (!static::check_enabled_type($type)) throw new InvalidArgumentException(__METHOD__.':First parameter is invalid.');
		if ($is_check_module_enabled && !is_enabled($type)) return false;

		return conf(sprintf('page.navbar.notification.cache.%s.unreadCount.isEnabled', $type), 'page', false);
	}

	public static function get_cahce_expire($type = 'notice')
	{
		if (!static::check_enabled_type($type)) throw new InvalidArgumentException(__METHOD__.':First parameter is invalid.');

		return conf(sprintf('page.navbar.notification.cache.%s.unreadCount.expire', $type), 'page');
	}

	public static function get_unread_count_cache_expire($type = 'common')
	{
		return conf(sprintf('page.navbar.notification.cache.%s.unreadCount.expire', $type), 'page');
	}

	public static function get_unread_count_cache_key($type, $member_id)
	{
		return conf(sprintf('page.navbar.notification.cache.%s.unreadCount.prefix', $type), 'page').$member_id;
	}

	public static function get_unread_count_cache($type, $member_id, $is_make_cache = false)
	{
		if (!static::check_enabled_type($type)) throw new InvalidArgumentException(__METHOD__.':First parameter is invalid.');

		$cache_key = static::get_unread_count_cache_key($type, $member_id);
		$cache_expir = static::get_unread_count_cache_expire();
		try
		{
			$unread_count = \Cache::get($cache_key, $cache_expir);
		}
		catch (\CacheNotFoundException $e)
		{
			$unread_count = null;
			if ($is_make_cache)
			{
				$unread_count = static::get_unread_count4member_id($type, $member_id);
				\Cache::set($cache_key, $unread_count, $cache_expir);
			}
		}

		return $unread_count;
	}

	protected static function get_unread_count4member_id($type, $member_id)
	{
		$model = static::get_model_to_get_unread_count($type);

		return $model::get_unread_count4member_id($member_id);
	}

	protected static function get_model_to_get_unread_count($type)
	{
		switch ($type)
		{
			case 'notice':
				return '\Notice\Model_NoticeStatus';
			case 'message':
				return '\Message\Model_MessageRecievedSummary';
				break;
		}

		throw new \InvalidArgumentException('Parameter is invalid.');
	}

	public static function delete_unread_count_cache($type, $member_id)
	{
		if (!static::check_enabled_type($type)) throw new InvalidArgumentException(__METHOD__.':First parameter is invalid.');

		$cache_key = static::get_unread_count_cache_key($type, $member_id);
		\Cache::delete($cache_key);
	}
}
