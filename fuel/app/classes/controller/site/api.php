<?php

class Controller_Site_Api extends Controller_Base_Site_Api
{
	public function before()
	{
		parent::before();
		$this->set_current_user();
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

			$val = Validation::forge();
			$response = View::forge('site/_parts/login', array('val' => $val, 'destination' => $destination));
			$status_code = 200;

			return Response::forge($response, $status_code);
		}
		catch(Exception $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
