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
		\Notice\Site_Util::change_notice_status2unread($foreign_table, $foreign_id, $member_id_to, $member_id_from, $type_key);
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
}
// End of file insertnotice.php
