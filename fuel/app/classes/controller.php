<?php

class Controller extends Fuel\Core\Controller
{
	protected $check_not_auth_action = array();

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

	protected function auth_check($redirect_uri = '')
	{
		if ($this->check_not_auth_action()) return;

		if (!Auth::check())
		{
			if ($redirect_uri)
			{
				Session::set_flash('destination', urlencode(Input::server('REQUEST_URI')));
				Response::redirect($redirect_uri);
			}

			return false;
		}

		return true;
	}
}
