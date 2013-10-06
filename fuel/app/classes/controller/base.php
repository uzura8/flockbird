<?php

class Controller_Base extends Controller_Hybrid
{
	public function before()
	{
		parent::before();

		// smartphone アクセス判定
		if (!defined('IS_SP')) define('IS_SP', Agent::is_smartphone());
		if (!defined('IS_API')) define('IS_API', Input::is_ajax());
	}

	protected function force_response($body = null, $status = 200)
	{
		$response = new Response($body, $status);
		$response->send(true);
		exit;
	}
}
