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
		$task_name_formatted = \Util_String::camelcase2ceparated($task_name, true);
		
		$is_error = false;
		$result   = null;
		$message  = '';
		try
		{
			\Site_Task::update_running_flag($task_name, true);

			$mail_handler = new \Site_Mail('notice');
			$bath_mail_handler = new \Notice\Site_MailBatchSender(array(
				'loop_max' => conf('noticeSendMail.loopMax', 'task'),
				'queues_limit' => conf('noticeSendMail.limit', 'task'),
			), $mail_handler);
			$send_count = $bath_mail_handler->execute();

			$result = true;
			$message = $send_count ? sprintf('%d mails sent', $send_count) : 'queues is empty';
			\Site_Task::update_running_flag($task_name, false);
		}
		catch(\TaskAlreadyRunningException $e)
		{
			$is_error = true;
			$result = 'warning';
		}
		catch(\Exception $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$is_error = true;
			$result = 'error';
			\Site_Task::update_running_flag($task_name, false);
		}
		if ($is_error)
		{
			if (!$message && !empty($e)) $message = $e->getMessage();
		}

		return \Site_Task::output_result_message($result, $task_name_formatted, $message, true);
	}
}

/* End of file tasks/sendmail.php */
