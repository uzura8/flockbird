<?php

class Controller_Base extends Controller_Template
{

	public function before()
	{
		parent::before();

		// Assign current_user to the instance so controllers can use it
		$this->current_user = Auth::check() ? Model_Member::find()->where('id', Auth::get_member_id())->related('memberauth')->get_one() : null;

		// Set a global variable so views can use it
		View::set_global('current_user', $this->current_user);
	}

	protected function check_not_auth_action()
	{
		return in_array(Request::active()->action, $this->check_not_auth_action);
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
