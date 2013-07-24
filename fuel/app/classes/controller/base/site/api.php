<?php

class SiteApiNotAuthorizedException extends \FuelException {}

class Controller_Base_Site_Api extends Controller_Base_Site
{
	public function before()
	{
		parent::before();
	}

	public function auth_check_api()
	{
		if (!$this->auth_check(false)) throw new \SiteApiNotAuthorizedException;
	}
}
