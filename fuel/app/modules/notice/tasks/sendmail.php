<?php
namespace Fuel\Tasks;

/**
 * Task SendMail
 */

class SendMail
{
	/**
	 * Usage (from command line):
	 *
	 * php oil r notice::sendmail
	 *
	 * @return string
	 */
	public static function run()
	{
		$task_name = 'noticeSendMail';
		$message_prefix = \Util_String::camelcase2ceparated($task_name, true);
		
		$result = '';
		$error_level = null;
		$error_message = '';
		try
		{
			\Site_Task::update_running_flag($task_name, true);

			$mail_handler = new \Site_Mail('notice');
			$bath_mail_handler = new \Notice\Site_MailBatchSender($mail_handler, array(
				'loop_max' => conf('noticeSendMail.loopMax', 'task'),
				'sleep_time' => conf('noticeSendMail.sleepTime', 'task'),
			));
			$result = $bath_mail_handler->execute();
		}
		catch(\TaskAlreadyRunningException $e)
		{
			$error_leverl = 'warning';
		}
		catch(\Exception $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$error_leverl = 'error';
		}
		\Site_Task::update_running_flag($task_name, false);

		if ($error_level)
		{
			if (!$error_message && !empty($e)) $error_message = $e->getMessage();
			return \Util_Task::output_message(sprintf('%s %s: %s', $message_prefix, $error_level, $error_message), $error_leverl);
		}

		return \Util_Task::output_result_message($result, $task_name, sprintf('%s is completed!.', $message_prefix));
	}
}

/* End of file tasks/filetmp.php */
