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
				// delete notice
				self::delete_notice_unread_cache($notice->id);
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
						// delete notice
						self::delete_notice_unread_cache($notice->id);
						$notice->delete();
					}
				}
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

	private static function delete_notice_unread_cache($notice_id)
	{
		// 事前に unread cache を削除
		if (\Config::get('notice.cache.unreadCount.isEnabled'))
		{
			$member_ids = \Notice\Model_NoticeStatus::get_col_array('member_id', array('where' => array('notice_id' => $notice_id)));
			foreach ($member_ids as $member_id) \Notice\Site_Util::delete_unread_count_cache($member_id);
		}
	}
}
// End of file deletenotice.php
