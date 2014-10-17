<?php
namespace MyOrm;

class Observer_InsertNotice extends \Orm\Observer
{
	protected $_relations;
	protected $_update_properties;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_update_properties = $props['update_properties'];
	}

	public function after_insert(\Orm\Model $obj)
	{
		if (!$this->check_properties()) return;
		$this->execute($obj);
	}

	private function execute($obj)
	{
		list($foreign_table, $foreign_id, $member_id_to, $member_id_from, $type_key) = self::get_variables($obj);

		// watch content
		if ($member_id_from != $member_id_to)
		{
			\Notice\Site_Util::regiser_watch_content($member_id_from, $foreign_table, $foreign_id, $type_key);
		}

		// notice
		if (!$notice_member_ids = \Notice\Site_Util::get_notice_target_member_ids($member_id_to, $member_id_from, $foreign_table, $foreign_id, $type_key))
		{
			return;
		}
		$obj_notice = \Notice\Model_Notice::check_and_create($foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type($type_key), $member_id_to);
		\Notice\Model_NoticeMemberFrom::check_and_create($obj_notice->id, $member_id_from);
		foreach ($notice_member_ids as $notice_member_id)
		{
			\Notice\Site_Util::update_notice_status2unread($notice_member_id, $obj_notice->id);
		}
	}

	private function check_properties()
	{
		return isset(
			$this->_update_properties['foreign_table'],
			$this->_update_properties['foreign_id'],
			$this->_update_properties['type_key'],
			$this->_update_properties['member_id_from'],
			$this->_update_properties['member_id_to']
		);
	}

	private function get_variables($obj)
	{
		foreach ($this->_update_properties as $property_to => $froms)
		{
			if (!is_array($froms))
			{
				$$property_to = $obj->{$froms};
			}
			else
			{
				foreach ($froms as $value_from => $type)
				{
					$$property_to = \Site_Model::get_value_for_observer_setting($obj, $value_from, $type);
				}
			}
		}

		return array($foreign_table, $foreign_id, $member_id_to, $member_id_from, $type_key);
	}
}
// End of file insertnotice.php
