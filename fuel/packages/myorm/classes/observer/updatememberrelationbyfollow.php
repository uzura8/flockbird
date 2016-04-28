<?php
namespace MyOrm;

class Observer_UpdateMemberRelationByFollow extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function before_save(\Model_MemberRelation $obj)
	{
		if (!$obj->is_changed('is_follow')) return;
		$obj_other = \Model_MemberRelation::get4member_id_from_to($obj->member_id_to, $obj->member_id_from);
		// at set follow
		if ($obj->is_follow)
		{
			if (!$obj_other || !$obj_other->is_follow) return;
			$this->make_friend_relation($obj, $obj_other);
		}
		// at unset follow
		else
		{
			if (!$obj_other || !$obj_other->is_follow) return;
			$this->delete_friend_relation($obj, $obj_other);
		}
	}

	private function make_friend_relation($obj, $obj_other)
	{
		if (!$obj->is_friend)
		{
			$obj->is_friend = 1;
			$obj->save();
		}
		if (!$obj_other->is_friend)
		{
			$obj_other->is_friend = 1;
			$obj_other->save();
		}
	}

	private function delete_friend_relation($obj, $obj_other)
	{
		if ($obj->is_friend)
		{
			$obj->is_friend = 0;
			$obj->save();
		}
		if ($obj_other->is_friend)
		{
			$obj_other->is_friend = 0;
			$obj_other->save();
		}
	}
}
// End of file updatememberrelationbyfollow.php
