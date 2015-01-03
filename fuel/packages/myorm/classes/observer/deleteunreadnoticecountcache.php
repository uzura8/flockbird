<?php
namespace MyOrm;

class Observer_DeleteUnreadNoticeCountCache extends \Orm\Observer
{
	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
	}

	public function before_delete(\Orm\Model $obj)
	{
		$member_ids = \Notice\Model_NoticeStatus::get_col_array('member_id', array('where' => array('notice_id' => $obj->id)));
		foreach ($member_ids as $member_id) \Notice\Site_Util::delete_unread_count_cache($member_id);
	}
}
// End of file deleteunreadnoticecountcache.php
