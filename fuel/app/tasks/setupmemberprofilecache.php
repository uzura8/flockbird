<?php
namespace Fuel\Tasks;

/**
 * Task SetupMemberProfileCache
 */

class SetupMemberProfileCache
{
	/**
	 * Usage (from command line):
	 *
	 * php oil r setupmemberprofilecache
	 *
	 * @return string
	 */
	public static function run()
	{
		$task_name = 'setupMemberProfileCache';
		$is_error = false;
		$result   = null;
		$message  = '';

		try
		{
			if (!conf('profile.useCacheTable.isEnabled', 'member'))
			{
				throw new \FuelException('Not use member_profile_cache table');
			}

			$batch_handler = new \Site_SetupMemberProfileCache(array(
				'task_name' => $task_name,
			));
			$save_count = $batch_handler->execute();
			$result = true;
			$message = $save_count ? sprintf('member_profile_cache %d saved', $save_count) : 'queues is empty';
		}
		catch(\Site_BatchInvalidOptionException $e)
		{
			$is_error = true;
			$result = 'error';
		}
		catch(\Site_BatchAlreadyRunningException $e)
		{
			$is_error = true;
			$result = 'warning';
		}
		catch(\Exception $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$is_error = true;
			$result = 'error';
			$batch_handler->update_running_flag_off();
		}
		if ($is_error)
		{
			if (!$message && !empty($e)) $message = $e->getMessage();
		}
		$task_name_formatted = \Util_String::camelcase2ceparated($task_name);

		return \Site_Task::output_result_message($result, $task_name_formatted, $message, true);
	}
}

/* End of file tasks/sendmail.php */
