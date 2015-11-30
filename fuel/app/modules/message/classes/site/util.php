<?php
namespace Message;

class Site_Util
{
	public static function get_accept_foreign_tables()
	{
		return array();
	}

	public static function get_type4key($type_key = null, $is_return_bool = false)
	{
		if (is_numeric($type_key))
		{
			$types = static::get_types(true);
			if (in_array($type_key, $types)) return $type_key;

			if ($is_return_bool) return false;
			throw new \InvalidArgumentException('Parameter is invalid.');
		}

		if (!$type = \Config::get('message.types.'.$type_key))
		{
			if ($is_return_bool) return false;
			throw new \InvalidArgumentException('Parameter is invalid.');
		}

		return $type;
	}

	public static function get_key4type($target_type = null)
	{
		if (!is_numeric($target_type))
		{
			$type_keys = static::get_type_keys();
			if (in_array($target_type, $type_keys)) return $target_type;

			throw new \InvalidArgumentException('Parameter is invalid.');
		}

		$types = static::get_types();
		foreach ($types as $key => $type)
		{
			if ($type == $target_type) return $key;
		}

		throw new \InvalidArgumentException('Parameter is invalid.');
	}

	public static function check_type($target_type, $type_key)
	{
		return $target_type == self::get_type4key($type_key);
	}

	public static function get_types($is_value_only = false)
	{
		$types = array_values(\Config::get('message.types'));

		return $is_value_only ? array_values($types) : $types;
	}

	public static function get_type_keys()
	{
		return array_keys(\Config::get('message.types'));
	}

	public static function get_talks4view($self_member_id = 0, $type_key = null, $related_id = 0, $params = array())
	{
		list($list, $next_id) = Site_Model::get_talks(
			$self_member_id,
			$type_key,
			$related_id,
			$params['max_id'],
			$params['limit'],
			$params['is_latest'],
			$params['is_desc'],
			$params['since_id']
		);
		$data = array(
			'list' => $list,
			'next_id' => $next_id,
			'since_id' => $params['since_id'] ?: 0,
			'is_display_load_before_link' => $params['max_id'] ? true : false,
		);

		return $data;
	}


	public static function get_no_data_talks()
	{
		return term('message.talks.view').'がありません。';
	}
}

