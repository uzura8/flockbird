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
}
