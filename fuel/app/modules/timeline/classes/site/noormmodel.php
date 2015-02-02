<?php
namespace Timeline;

class Site_NoOrmModel
{
	public static function delete_timeline4member_id($member_id)
	{
		if (!$limit) $limit = conf('batch.limit.delete.timeline');
		while ($timeline_ids = \Util_Db::conv_col(DB::select('id')->from('timeline')->where('member_id', $member_id)->as_assoc()->execute()))
		{
			foreach ($timeline_ids as $timeline_id) static::delete_timeline4id($timeline_id);
		}
	}

	public static function delete_timeline4id($timeline_id)
	{
		$delete_target_notice_cache_member_ids = array();
		$writable_connection = \MyOrm\Model::connection(true);
		\DBUtil::set_connection($writable_connection);
		\DB::start_transaction();
		if (is_enabled('notice'))
		{
			\Notice\Site_NoOrmModel::delete_member_watch_content_multiple4foreign_data('timeline', $timeline_id);
			$notice_ids = \Notice\Site_NoOrmModel::get_notice_ids4foreign_data('timeline', $timeline_id);
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

	public static function delete_timeline_multiple4foreign_data($foreign_table, $foreign_id)
	{
		$timeline_child_data_timeline_ids = static::get_timeline_child_data_timeline_ids4foreign_data($foreign_table, $foreign_id);
		static::delete_timeline_child_data4timeline_ids($timeline_child_data_timeline_ids);
		$timeline_ids = static::get_timeline_ids4foreign_data($foreign_table, $foreign_id);
		static::delete_timeline4ids($timeline_ids);

		return array_unique($timeline_child_data_timeline_ids + $timeline_ids);
	}

	public static function get_timeline_child_data_timeline_ids4foreign_data($foreign_table, $foreign_id)
	{
		return \Util_Db::conv_col(\DB::select('timeline_id')->from('timeline_child_data')
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id)
			->execute()
			->as_array());
	}

	public static function delete_timeline_child_data4timeline_ids($timeline_ids)
	{
		if (!$timeline_ids) return false;

		foreach ($timeline_ids as $timeline_id) \Timeline\Site_Util::delete_cache($timeline_id);

		return \DB::delete('timeline_child_data')
			->where('timeline_id', 'in', $timeline_ids)
			->execute();
	}

	public static function get_timeline_ids4foreign_data($foreign_table, $foreign_id)
	{
		return \Util_Db::conv_col(\DB::select('id')->from('timeline')
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id)
			->execute()
			->as_array());
	}

	public static function delete_timeline4ids($timeline_ids)
	{
		if (!$timeline_ids) return false;

		foreach ($timeline_idis as $timeline_id) \Timeline\Site_Util::delete_cache($timeline_id);

		return \DB::delete('timeline')
			->where('id', 'in', $timeline_ids)
			->execute();
	}
}
