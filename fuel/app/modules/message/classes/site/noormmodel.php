<?php
namespace Message;

class Site_NoOrmModel
{
	public static function delete_message_recieved4member_id($member_id, $limit = 0)
	{
		if (!$limit) $limit = conf('batch.limit.delete.message');

		// delete message_recieved_summary
		while ($message_recieved_summary_ids
			= \Util_Db::conv_col(\DB::select('id')->from('message_recieved_summary')->where('member_id', $member_id)->limit($limit)->as_assoc()->execute()))
		{
			foreach ($message_recieved_summary_ids as $message_recieved_summary_id) static::delete_message_recieved_summary4id($message_recieved_summary_id);
		}

		// delete message_recieved
		while ($message_recieved_ids
			= \Util_Db::conv_col(\DB::select('id')->from('message_recieved')->where('member_id', $member_id)->limit($limit)->as_assoc()->execute()))
		{
			foreach ($message_recieved_ids as $message_recieved_id) static::delete_message_recieved4id($message_recieved_id);
		}
	}

	public static function delete_message_recieved_summary4id($message_recieved_summary_id)
	{
		$writable_connection = \MyOrm\Model::connection(true);
		\DBUtil::set_connection($writable_connection);
		\DB::start_transaction();
		if (!\DB::delete('message_recieved_summary')->where('id', $message_recieved_summary_id)->execute())
		{
			throw new \FuelException('Failed to delete message_recieved_summary. id:'.$message_recieved_summary_id);
		}
		\DB::commit_transaction();
		\DBUtil::set_connection(null);
	}

	public static function delete_message_recieved4id($message_recieved_id)
	{
		$writable_connection = \MyOrm\Model::connection(true);
		\DBUtil::set_connection($writable_connection);
		\DB::start_transaction();
		if (!\DB::delete('message_recieved')->where('id', $message_recieved_id)->execute())
		{
			throw new \FuelException('Failed to delete message_recieved. id:'.$message_recieved_id);
		}
		\DB::commit_transaction();
		\DBUtil::set_connection(null);
	}
}
