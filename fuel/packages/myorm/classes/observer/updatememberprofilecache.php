<?php
namespace MyOrm;

class Observer_UpdateMemberProfileCache extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function after_save(\MyOrm\Model $obj)
	{
		$table_name = $obj::get_table_name();
		if (!in_array($table_name, array('member', 'member_profile'))) throw new \FuelException('table is invalid.');

		$record_builder = new \Site_MemberProfileCacheBuilder;
		$method = 'save_for_'.$table_name;
		$member_id = $table_name == 'member_profile' ? $obj->member_id : $obj->id;
		$record_builder->$method($member_id);
	}

	public function after_delete(\MyOrm\Model $obj)
	{
		$table_name = $obj::get_table_name();
		if ($table_name !='member_profile') throw new \FuelException('table is invalid.');

		$record_builder = new \Site_MemberProfileCacheBuilder;
		$record_builder->delete_for_member_profile($obj->member_id, $obj->profile_id);
	}
}
// End of file updatememberprofilecache.php

