<?php

class Controller_Base extends Controller_Template
{

	public function before()
	{
		parent::before();
	}

	protected function display_error($message_display = '', $messsage_log = '', $action = 'error/500', $status = 500)
	{
		if ($messsage_log) \Log::error($messsage_log);
		$this->template->title = ($message_display) ? $message_display : 'Error';
		$this->template->header_title = site_title($this->template->title);
		$this->template->content = View::forge($action);
		if ($status) $this->response->status = $status;
	}
}
