<?php
namespace MyOrm;

class Observer_UpdateProfile extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function after_insert(\Model_Profile $obj)
	{
		\Site_MemberProfileCacheBuilder::add_profile_field($obj);
	}

	public function before_update(\Model_Profile $obj)
	{
		if (!self::check_changed($obj)) return;

		\Site_MemberProfileCacheBuilder::modify_profile_field($obj);
	}

	public function before_delete(\Model_Profile $obj)
	{
		\Site_MemberProfileCacheBuilder::drop_profile_field($obj->name);
	}

	protected static function check_changed(\Model_Profile $obj)
	{
		if ($obj->is_changed('name')) return true;
		if ($obj->is_changed('value_max')) return true;

		$fields = \DB::list_columns('member_profile_cache');
		if (!isset($fields[$obj->name])) return true;

		if ($obj->is_changed('form_type'))
		{
			$type = $fields[$obj->name]['type'];
			switch ($obj->form_type)
			{
				case 'checkbox':
					return true;
				case 'select':
				case 'radio':
					if ($type  != 'int') return true;
					break;
				case 'input':
				case 'textarea':
					if ($type  != 'string') return true;
					break;
			}
		}

		return false;
	}
}
// End of file updateprofile.php

