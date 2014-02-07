<?php
namespace Admin;

class Controller_Api extends Controller_Base
{
	public function before()
	{
		parent::before();

		$this->auth_check_api(true);
		$this->set_current_user();
	}
}
