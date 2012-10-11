<?php

class Controller_Site_Api extends Controller_Base_Api
{
	public function before()
	{
		parent::before();
		$this->auth_check_api();
	}

	public function auth_check_api()
	{
		if ($this->auth_check()) return;

		$this->response(array('error' => 'Not Authorized'), 401);
	}

	private function set_current_user()
	{
		$auth = Auth::instance();
		$this->u = Auth::check() ? Model_Member::find()->where('id', $auth->get_member_id())->related('memberauth')->get_one() : null;

		View::set_global('u', $this->u);
	}
}
