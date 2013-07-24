<?php

class Controller_Base extends Controller_Hybrid
{
	public function before()
	{
		parent::before();
	}

	protected function force_response($body = null, $status = 200)
	{
		$response = new Response($body, $status);
		$response->send(true);
		exit;
	}
}
