<?php
namespace MyOrm;

class Observer_DeleteNotice extends \Orm\Observer
{
	protected $_conditions;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_conditions = $props['conditions'];
	}

	public function before_delete(\Orm\Model $obj)
	{
		$this->execute($obj);
	}

	private function execute(\Orm\Model $obj)
	{
		// 親記事削除時
		if (empty($this->_conditions['type']))
		{
			// delete member_watch_content
			self::delete_member_watch_content($obj, $this->_conditions);
		}

		$notices = \Site_Model::get4relation('\Notice\Model_Notice', $this->_conditions, $obj);
		foreach ($notices as $notice)
		{
			// 親記事削除時
			if (empty($this->_conditions['type']))
			{
				$notice->delete();
			}
			// 下位コンテンツ削除時
			else
			{
				// delete notice_member_from
				if (self::delete_notice_member_from($notice->id, $obj->member_id))
				{
					$parent_content_member_id = \Site_Model::get_value4table_and_id($notice->foreign_table, $notice->foreign_id, 'member_id');
					if (!\Notice\Model_NoticeMemberFrom::get_count4notice_id($notice->id, $parent_content_member_id))
					{
						$notice->delete();
					}
				}
			}
		}

		// 親記事削除時
		if (empty($this->_conditions['type']))
		{
			$foreign_table = \Util_Array::get_first_key($this->_conditions['foreign_table']);
			$foreign_id = $obj->id;
			$notices = \Notice\Model_Notice::get4parent_data($foreign_table, $foreign_id);
			foreach ($notices as $notice) $notice->delete();
		}
		// 下位コンテンツ削除時
		else
		{
			$type = \Util_Array::get_first_key($this->_conditions['type']);
			// comment 削除時
			if ($type == \Notice\Site_Util::get_notice_type('comment'))
			{
				$foreign_table = \Util_Array::get_first_key($this->_conditions['foreign_table']).'_comment';
				$foreign_id = $obj->id;
				$notices = \Notice\Model_Notice::get4foreign_data($foreign_table, $foreign_id);
				foreach ($notices as $notice) $notice->delete();
			}
		}
	}

	private static function delete_member_watch_content($obj, $conditions)
	{
		if (!$member_watch_contents = \Site_Model::get4relation('\Notice\Model_MemberWatchContent', $conditions, $obj)) return false;
		foreach ($member_watch_contents as $member_watch_content) $member_watch_content->delete();
	}

	private static function delete_notice_member_from($notice_id, $member_id)
	{
		if (!$notice_member_from = \Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_id, $member_id)) return false;

		return $notice_member_from->delete();
	}
}
// End of file deletenotice.php
