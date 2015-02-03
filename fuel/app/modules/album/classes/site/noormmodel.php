<?php
namespace Album;

class Site_NoOrmModel
{
	public static function delete_album4member_id($member_id, $with_delete_timeline = false, $limit = 0)
	{
		if (!$limit) $limit = conf('batch.limit.delete.album');
		while ($album_ids = \Util_Db::conv_col(\DB::select('id')->from('album')->where('member_id', $member_id)->limit($limit)->as_assoc()->execute()))
		{
			foreach ($album_ids as $album_id) static::delete_album4id($album_id, $with_delete_timeline);
		}
	}

	public static function delete_album4id($album_id, $with_delete_timeline = false)
	{
		static::delete_album_image_multiple4album_id($album_id, $with_delete_timeline);

		$delete_target_notice_cache_member_ids = array();
		$delete_target_timeline_ids = array();
		$writable_connection = \MyOrm\Model::connection(true);
		\DBUtil::set_connection($writable_connection);
		\DB::start_transaction();
		if (is_enabled('notice'))
		{
			\Notice\Site_NoOrmModel::delete_member_watch_content_multiple4foreign_data('album', $album_id);
			$notice_ids = \Notice\Site_NoOrmModel::get_notice_ids4foreign_data('album', $album_id);
			$delete_target_notice_cache_member_ids = \Notice\Site_NoOrmModel::get_notice_status_member_ids4notice_ids($notice_ids);
			\Notice\Site_NoOrmModel::delete_notice_multiple4ids($notice_ids);
		}
		if (is_enabled('timeline') && $with_delete_timeline)
		{
			$delete_target_timeline_ids = \timeline\site_noormmodel::delete_timeline_multiple4foreign_data('album', $album_id);
		}
		if (!\DB::delete('album')->where('id', $album_id)->execute())
		{
			throw new \FuelException('Failed to delete album. id:'.$album_id);
		}
		\DB::commit_transaction();
		\DBUtil::set_connection(null);

		// delete caches
		if ($delete_target_notice_cache_member_ids)
		{
			foreach ($delete_target_notice_cache_member_ids as $member_id) \Notice\Site_Util::delete_unread_count_cache($member_id);
		}
		if ($delete_target_timeline_ids)
		{
			foreach ($delete_target_timeline_ids as $timeline_id) \Timeline\Site_Util::delete_cache($timeline_id);
		}
	}

	public static function delete_album_image_multiple4album_id($album_id, $with_delete_timeline = false, $limit = 0)
	{
		if (!$limit) $limit = conf('batch.limit.delete.album_image');
		while ($album_image_ids = \Util_Db::conv_col(\DB::select('id')->from('album_image')->where('album_id', $album_id)->limit($limit)->as_assoc()->execute()))
		{
			static::delete_album_image_multiple4ids($album_image_ids, $with_delete_timeline);
		}
	}

	public static function delete_album_image_multiple4ids($album_image_ids = array(), $with_delete_timeline = false)
	{
		if (!is_array($album_image_ids)) $album_image_ids = (array)$album_image_ids;
		$delete_target_notice_cache_member_ids = array();
		$delete_target_timeline_ids = array();

		$writable_connection = \MyOrm\Model::connection(true);
		\DBUtil::set_connection($writable_connection);
		\DB::start_transaction();
		foreach ($album_image_ids as $album_image_id)
		{
			if (is_enabled('notice'))
			{
				\Notice\Site_NoOrmModel::delete_member_watch_content_multiple4foreign_data('album_image', $album_image_id);
				$notice_ids = \Notice\Site_NoOrmModel::get_notice_ids4foreign_data('album_image', $album_image_id);
				$delete_target_notice_cache_member_ids += \Notice\Site_NoOrmModel::get_notice_status_member_ids4notice_ids($notice_ids);
				\Notice\Site_NoOrmModel::delete_notice_multiple4ids($notice_ids);
			}
			if (is_enabled('timeline') && $with_delete_timeline)
			{
				$delete_target_timeline_ids += \timeline\site_noormmodel::delete_timeline_multiple4foreign_data('album_image', $album_image_id);
			}
		}

		$file_names = \Util_Orm::conv_col2array(Model_AlbumImage::get4ids($album_image_ids), 'file_name');
		\DB::delete('album_image')
			->where('id', 'in', $album_image_ids)
			->execute();
		\DB::commit_transaction();
		\DBUtil::set_connection(null);

		\DB::start_transaction();
		if ($files = \Model_File::get4names($file_names))
		{
			foreach ($files as $file) $file->delete();
		}
		\DB::commit_transaction();

		// delete caches
		if ($delete_target_notice_cache_member_ids)
		{
			foreach ($delete_target_notice_cache_member_ids as $member_id) \Notice\Site_Util::delete_unread_count_cache($member_id);
		}
		if ($delete_target_timeline_ids)
		{
			foreach ($delete_target_timeline_ids as $timeline_id) \Timeline\Site_Util::delete_cache($timeline_id);
		}
	}
}
