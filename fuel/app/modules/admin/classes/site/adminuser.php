<?php
namespace Admin;

class Site_AdminUser
{
	const GROUP_USER      = 1;
	const GROUP_MODERATOR = 50;
	const GROUP_ADMIN     = 100;

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

	public static function get_admin_user_roles($admin_user_group_infos)
	{
		if (!$admin_user_group_infos) throw new \FuelException('Failed to get groups.');

		$admin_user_group = null;
		foreach ($admin_user_group_infos as $group_info)
		{
			if (empty($group_info[0]) || $group_info[0] != 'Simplegroup') continue;
			$admin_user_group = $group_info[1];
		}
		if (!$admin_user_group) throw new \FuelException('Failed to get SimpleAuth groups.');
		$simple_auth_groups = \Config::get('simpleauth.groups');
		if (!isset($simple_auth_groups[$admin_user_group]['roles'])) throw new \FuelException('Failed to get SimpleAuth group role.');

		return $simple_auth_groups[$admin_user_group]['roles'];
	}

	public static function get_editable_member_group_keys4admin_user_groups($admin_user_groups)
	{
		if (!$admin_roles = static::get_admin_user_roles($admin_user_groups)) return array();

		$member_group_editable_roles = conf('member.group.edit.roles', 'admin');
		$editable_member_group_keys = array();
		foreach ($admin_roles as $admin_role)
		{
			if (empty($member_group_editable_roles[$admin_role])) continue;
			$member_group_keys = $member_group_editable_roles[$admin_role];
			if ($member_group_keys === true) return \Site_Member::get_group_keys();
			$editable_member_group_keys += $member_group_keys;
		}

		return array_unique($editable_member_group_keys);
	}

	public static function check_editable_member_group($admin_user_groups, $member_group_key)
	{
		$editable_member_group_keys = static::get_editable_member_group_keys4admin_user_groups($admin_user_groups);

		return in_array($member_group_key, $editable_member_group_keys);
	}

	public static function get_editable_member_groups($admin_user_groups)
	{
		$groups = \Site_Member::get_groups();
		$editable_member_group_keys = static::get_editable_member_group_keys4admin_user_groups($admin_user_groups);
		foreach ($groups as $key => $value)
		{
			if (!in_array($key, $editable_member_group_keys)) unset($groups[$key]);
		}

		return $groups;
	}
}
