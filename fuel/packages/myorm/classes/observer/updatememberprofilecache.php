<?php
namespace MyOrm;

class Observer_UpdateMemberProfileCache extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function after_update(\MyOrm\Model $obj)
	{
		$this->execute($obj);
	}

	private function execute($obj)
	{
		$table_name = $obj::get_table_name();
		if (!in_array($table_name, array('member', 'member_profile'))) throw new \FuelException('table is invalid.');

		$record_builder = new \Site_MemberProfileCacheBuilder;
		$method = 'save_for_'.$table_name;
		$member_id = $table_name == 'member_profile' ? $obj->member_id : $obj->id;
		$record_builder->$method($member_id);
	}
}
// End of file updatememberprofilecacheformember.php

