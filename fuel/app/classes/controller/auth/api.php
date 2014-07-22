<?php

class Controller_Auth_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_login',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Api login
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_login()
	{
		if ($this->format != 'html') throw new HttpNotFoundException();

		$response = '';
		try
		{
			$destination = Input::get('destination', '');
			$response = View::forge('auth/_parts/login', array('destination' => $destination));
			$status_code = 200;

			return Response::forge($response, $status_code);
		}
		catch(FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
