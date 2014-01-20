<?php
namespace MyOrm;

class Observer_InsertMemberFollowTimeline extends \Orm\Observer
{
	protected $_timeline_relations;
	protected $_property_from_member_id;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_timeline_relations = $props['timeline_relations'];
		$this->_property_from_member_id = $props['property_from_member_id'];
	}

	public function after_insert(\Orm\Model $obj)
	{

		if (!$timeline = $this->get_timeline($obj)) return false;
		if ($this->check_already_exists($timeline->id, $obj->{$this->_property_from_member_id})) return false;

		$member_follow_timeline = new \Timeline\Model_MemberFollowTimeline();
		$member_follow_timeline->timeline_id = $timeline->id;
		$member_follow_timeline->member_id = $obj->{$this->_property_from_member_id};

		return $member_follow_timeline->save();
	}

	protected function get_timeline(\Orm\Model $obj)
	{
		$query = \Timeline\Model_Timeline::query();
		foreach ($this->_timeline_relations as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				switch ($type)
				{
					case 'value':
						$value = $value_from;
						break;
					case 'property':
						$value = $obj->{$value_from};
						break;
					default :
						throw new \FuelException('Orm observer setting error.');
						break;
				}
				$query = $query->where($property_to, $value);
			}
		}

		return $query->get_one();
	}

	protected function check_already_exists($timeline_id, $member_id)
	{
		return (bool)\Timeline\Model_MemberFollowTimeline::get4timeline_id_and_member_id($timeline_id, $member_id);
	}
}
// End of file insertmemberfollowtimeline.php
