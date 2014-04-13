<?php
class DisableToUpdatePublicFlagException extends \FuelException {}

class Controller_Site_Api extends Controller_Base_Site
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}
}
