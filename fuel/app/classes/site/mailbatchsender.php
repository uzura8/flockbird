<?php

abstract class Site_MailBatchSender
{
	protected $options = array();
	protected $status_flags = array();
	protected $loop_count = 0;
	protected $send_count = 0;
	protected $mail_handler;
	protected $each_queue;
	protected $each_result = false;
	protected $each_error_message = '';
	protected $mail_data = array();
	//protected $is_trunsuction_commit_turn = false;

	public function __construct($mail_handler = null, $options = array())
	{
		$this->status_flags = conf('default.statusFlags', 'task');
		$this->mail_handler = $mail_handler;
		$this->options = array(
			'is_admin' => false,
			'loop_max' => conf('default.loopMax', 'task'),
			'sleep_time' => conf('default.sleepTime', 'task'),
			//'debug_log_is_enabled' => conf('mail.log.develop.isEnabled'),
			//'debug_log_file_path' => conf('mail.log.develop.file_path'),
		);
		$this->setup_options($options);
	}

	protected function setup_options($options)
	{
		if ($options) $this->options = $options + $this->options;
	}

	public function execute()
	{
		while ($this->loop_count < $this->options['loop_max'])
		{
			if (!$queues = $this->get_queues()) break;

			foreach ($queues as $key => $queue)
			{
				$this->each_queue = $queue;
				unset($queue);
				try
				{
					$this->reset_each_status();
					$this->set_mail_data();
					$this->send_mail();
					\DB::start_transaction();
					$this->update_status();
					\DB::commit_transaction();
				}
				catch(\Exception $e)
				{
					if (\DB::in_transaction()) \DB::rollback_transaction();
				}
				unset($queues[$key]);
			}
			if (\DB::in_transaction()) \DB::commit_transaction();
			sleep($this->options['sleep_time']);
			$this->loop_count++;
		}
		unset($queues);

		return $this->send_count;
	}

	protected function reset_each_status()
	{
		$this->each_result = null;
		$this->each_error_message = '';
	}

	protected function send_mail()
	{
		if (!$this->mail_data) return;

		$error_message = 'send mail error: ';
		try
		{
			$this->mail_handler->send(null, $this->mail_data);
			$this->send_count++;
			$this->each_result = $this->get_status_value('successed');
			return;
		}
		catch(\EmailValidationFailedException $e)
		{
			//Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
			$error_message .= '[EmailValidationFailedException] ';
		}
		catch(\EmailSendingFailedException $e)
		{
			//Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
			$error_message .= '[EmailSendingFailedException] ';
		}
		catch(\Exception $e)
		{
			$error_message .= '[Exception] ';
		}
		if (isset($e)) $error_message .= $e->getMessage();
		$this->each_result = $this->get_status_value('failed');
		$this->each_error_message = $error_message;
	}

	protected function get_status_value($key)
	{
		if (!isset($this->status_flags[$key])) throw new InvalidArgumentException('Parameter is invalid.');
		return $this->status_flags[$key];
	}

	protected function update_status()
	{
		if (is_null($this->each_result))
		{
			$this->each_queue->status = $this->get_status_value('unexecuted');
		}
		else
		{
			$this->each_queue->status = $this->each_result;
		}
		if ($this->each_error_message) $this->each_queue->result_message = $this->each_error_message;
		$this->each_queue->save();
	}

	abstract protected function get_queues();
	abstract protected function set_mail_data();
}

