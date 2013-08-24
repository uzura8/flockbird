<?php

class SiteApiNotAuthorizedException extends \FuelException {}
class DisableToUpdatePublicFlagException extends \FuelException {}

class Controller_Base_Site_Api extends Controller_Base_Site
{
	public function before()
	{
		parent::before();
	}

	public function auth_check_api($is_force_response = false)
	{
		if (!$this->auth_check(true))
		{
			if ($is_force_response)
			{
				$this->force_response(0, 401);
			}
			else
			{
				throw new SiteApiNotAuthorizedException;
			}
		}
	}
}
