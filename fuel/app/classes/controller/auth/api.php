<?php

class Controller_Auth_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array(
		//'get_login',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * get_login
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	//public function get_login()
	//{
	//	$this->api_accept_formats = 'html';
	//	$this->controller_common_api(function() {
	//		$destination = Input::get('destination', '');
	//		$this->response_body = View::forge('auth/_parts/login', array('destination' => $destination));

	//		return $this->response_body;
	//	});
	//}
}
