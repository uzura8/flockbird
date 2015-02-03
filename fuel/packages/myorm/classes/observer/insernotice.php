<?php
namespace MyOrm;

class Observer_InsertNotice extends \Orm\Observer
{
	protected $_relations;
	protected $_update_properties;
	protected $_executed_params;

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
		if (self::check_already_executed($foreign_table, $foreign_id, $member_id_to, $member_id_from, $type_key)) return;

		if (!\Model_Member::check_is_active($member_id_to)) $member_id_to = null;

		// watch content
		if ($member_id_to && $member_id_from != $member_id_to)
		{
			\Notice\Site_Util::regiser_watch_content($member_id_from, $foreign_table, $foreign_id, $type_key);
		}

		// get mention targets
		$mentioned_member_ids = array();
		if (conf('mention.isEnabled', 'notice') && \Notice\Site_Util::check_mention_target($foreign_table, $type_key))
		{
			$body_porp = !empty($this->_update_properties['body_prop_name']) ? $this->_update_properties['body_prop_name'] : 'body';
			$mentioned_member_ids = \Notice\Site_Model::get_mentioned_member_ids4body($obj->{$body_porp});
			$mentioned_member_ids = \Util_Array::delete_in_array($mentioned_member_ids, array($member_id_from));// 自分宛の mention は無効
		}
		// get notice targets
		$notice_member_ids = \Notice\Site_Util::get_notice_target_member_ids($member_id_to, $member_id_from, $foreign_table, $foreign_id, $type_key);

		// 重複通知防止
		$notice_member_ids = \Util_Array::delete_in_array($notice_member_ids, $mentioned_member_ids);
		if (!$notice_member_ids && !$mentioned_member_ids) return;

		// notice 実行
		if ($notice_member_ids)
		{
			\Notice\Site_Model::execut_notice($foreign_table, $foreign_id, $type_key, $member_id_from, $notice_member_ids);
		}
		// mention 実行
		if ($mentioned_member_ids)
		{
			$mention_type_key = $type_key == 'comment' ? 'comment_mention' : 'parent_mention';
			\Notice\Site_Model::execut_notice($foreign_table, $foreign_id, $mention_type_key, $member_id_from, $mentioned_member_ids);
		}
	}

	private function check_properties()
	{
		return isset(
			$this->_update_properties['foreign_table'],
			$this->_update_properties['foreign_id'],
			$this->_update_properties['type_key'],
			$this->_update_properties['member_id_from']
		);
	}

	/**
	* 実行済みの処理を重複して実行しない
	*/
	private function check_already_executed($foreign_table, $foreign_id, $member_id_to, $member_id_from, $type_key)
	{
		if (!self::check_is_target_already_executed($foreign_table, $type_key)) return false;

		$params = array(
			'foreign_table' => $foreign_table,
			'foreign_id' => $foreign_id,
			'member_id_to' => $member_id_to,
			'member_id_from' => $member_id_from,
			'type_key' => $type_key,
		);
		if (!$this->_executed_params)
		{
			$this->_executed_params = $params;

			return false;
		}

		if ($this->_executed_params != $params)
		{
			$this->_executed_params = $params;

			return false;
		}

		return true;
	}

	private function check_is_target_already_executed($foreign_table, $type_key)
	{
		if ($foreign_table == 'album' && $type_key == 'child_data') return true;

		return false;
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
		if (!isset($member_id_to)) $member_id_to = 0;

		return array($foreign_table, $foreign_id, $member_id_to, $member_id_from, $type_key);
	}
}
// End of file insertnotice.php
