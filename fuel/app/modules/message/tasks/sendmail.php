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
	 * php oil r message::sendmail
	 *
	 * @return string
	 */
	public static function run()
	{
		$task_name = 'messageSendMail';
		$is_error = false;
		$result   = null;
		$message  = '';

		$mail_handler = new \Site_Mail('message');
		$batch_mail_handler = new \Message\Site_MailBatchSender(array(
			'task_name' => $task_name,
			'loop_max' => conf($task_name.'.loopMax', 'task'),
			'queues_limit' => conf($task_name.'.limit', 'task'),
		), $mail_handler);
		try
		{
			$send_count = $batch_mail_handler->execute();
			$result = true;
			$message = $send_count ? sprintf('%d mails sent', $send_count) : 'queues is empty';
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
			$batch_mail_handler->update_running_flag_off();
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
