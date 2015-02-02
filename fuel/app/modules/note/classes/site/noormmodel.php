<?php
namespace Note;

class Site_NoOrmModel
{
	public static function delete_note4member_id($member_id)
	{
		if (!$limit) $limit = conf('batch.limit.delete.note');
		while ($note_ids = \Util_Db::conv_col(DB::select('id')->from('note')->where('member_id', $member_id)->as_assoc()->execute()))
		{
			foreach ($note_ids as $note_id) static::delete_note4id($note_id);
		}
	}

	public static function delete_note4id($note_id)
	{
		$delete_target_notice_cache_member_ids = array();
		$writable_connection = \MyOrm\Model::connection(true);
		\DBUtil::set_connection($writable_connection);
		\DB::start_transaction();
		if (is_enabled('notice'))
		{
			\Notice\Site_NoOrmModel::delete_member_watch_content_multiple4foreign_data('note', $note_id);
			$notice_ids = \Notice\Site_NoOrmModel::get_notice_ids4foreign_data('note', $note_id);
			$delete_target_notice_cache_member_ids = \Notice\Site_NoOrmModel::get_notice_status_member_ids4notice_ids($notice_ids);
			\Notice\Site_NoOrmModel::delete_notice_multiple4ids($notice_ids);
		}
		\DB::commit_transaction();
		\DBUtil::set_connection(null);

		// delete caches
		if ($delete_target_notice_cache_member_ids)
		{
			foreach ($delete_target_notice_cache_member_ids as $member_id) \Notice\Site_Util::delete_unread_count_cache($member_id);
		}
	}
}
