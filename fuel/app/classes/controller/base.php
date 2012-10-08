<?php

class Controller_Base extends Controller_Template
{

	public function before()
	{
		parent::before();
		$this->response = new Response();

		// クリックジャッキング対策
		$this->response->set_header('X-FRAME-OPTIONS', 'SAMEORIGIN');
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

	protected function auth_check()
	{
		if (!$this->check_not_auth_action() && !Auth::check())
		{
			Session::set_flash('destination', urlencode(Input::server('REQUEST_URI')));
			Response::redirect('site/login');
		}
	}
}
