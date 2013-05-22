<?php

class Controller_Site_Api extends Controller_Base_Api
{
	public function before()
	{
		parent::before();
	}

	public function auth_check_api()
	{
		if (!$this->auth_check()) throw new \SiteApiNotAuthorizedException;
		$this->set_current_user();
	}

	private function set_current_user()
	{
		$auth = Auth::instance();
		$this->u = Auth::check() ? Model_Member::find()->where('id', $auth->get_member_id())->related('memberauth')->get_one() : null;

		View::set_global('u', $this->u);
	}
}

class SiteApiNotAuthorizedException extends \FuelException {}
