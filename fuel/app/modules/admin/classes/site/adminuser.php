<?php
namespace Admin;

class Site_AdminUser
{
	public static function check_gruop($target_group_key, $more_than = 0, $is_equal = false)
	{
		if ($is_equal) return $target_group_key == $more_than;

		return $target_group_key >= $more_than;
	}

	public static function get_gruop_name($target_group_key, $is_simple = false)
	{
		$key_prefix = 'admin.user.groups.type';
		if ($is_simple) $key_prefix .= '_simple';
		$key_prefix .= '.';

		return term($key_prefix.$target_group_key);
	}
}
