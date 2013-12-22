<?php

class Controller_Site_Api extends Controller_Base_Site_Api
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();

		$this->auth_check_api(true);
		$this->set_current_user();
	}
}
