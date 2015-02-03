<?php
namespace Notice;

class Site_NoOrmModel
{
	public static function delete_member_watch_content_multiple4foreign_data($relational_table, $relational_id)
	{
		return \DB::delete('member_watch_content')
			->where('foreign_table', $relational_table)
			->where('foreign_id', $relational_id)
			->execute();
	}

	public static function get_notice_ids4foreign_data($relational_table, $relational_id)
	{
		$notice_ids = \Util_Db::conv_col(\DB::select('id')->from('notice')
			->where('foreign_table', $relational_table)
			->where('foreign_id', $relational_id)
			->execute()
			->as_array());
		$notice_ids += \Util_Db::conv_col(\DB::select('id')->from('notice')
			->where('parent_table', $relational_table)
			->where('parent_id', $relational_id)
			->execute()
			->as_array());

		return array_unique($notice_ids);
	}

	public static function get_notice_status_member_ids4notice_ids($notice_ids)
	{
		if (!$notice_ids) return array();

		return \Util_Db::conv_col(\DB::select('notice_id')->from('notice_status')
			->where('notice_id', 'in', $notice_ids)
			->execute()
			->as_array());
	}

	public static function delete_notice_multiple4ids($notice_ids)
	{
		if (!$notice_ids) return array();

		return \DB::delete('notice')
			->where('id', 'in', $notice_ids)
			->execute();
	}
}
