<?php

class Controller_Base extends Controller_Template
{

	public function before()
	{
		parent::before();

		// Assign current_user to the instance so controllers can use it
		$this->current_user = Auth::check() ? Model_Member::find()->where('id', Auth::get_member_id())->related('memberauth')->get_one() : null;

		// Set a global variable so views can use it
		View::set_global('current_user', $this->current_user);
	}

	protected function check_not_auth_action()
	{
		return in_array(Request::active()->action, $this->check_not_auth_action);
	}
}
