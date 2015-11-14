<?php

abstract class Site_MailBatchSender extends Site_BatchHandler
{
	protected $status_flags = array();
	protected $send_count = 0;
	protected $mail_handler;
	protected $mail_data = array();

	public function __construct($options = array(), $mail_handler = null)
	{
		parent::__construct($options);
		$this->options = $this->options + array(
			'queues_limit' => conf('default.limit.sendMail', 'task'),
		);
		$this->status_flags = conf('default.statusFlags', 'task');
		$this->mail_handler = $mail_handler;
	}

	protected function execute_each()
	{
		$this->set_mail_data();
		$this->send_mail();
		\DB::start_transaction();
		$this->update_status();
		\DB::commit_transaction();
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
			$error_message .= '[EmailValidationFailedException] ';
		}
		catch(\EmailSendingFailedException $e)
		{
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

	protected function get_result()
	{
		return $this->send_count;
	}

	abstract protected function set_mail_data();
}

