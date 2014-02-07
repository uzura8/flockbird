<?php
namespace Admin;

class Controller_Base extends \Controller_Base {

	public function before()
	{
		parent::before();
	}

	protected function set_current_user()
	{
		$this->u = null;
		if (\Auth::check())
		{
			$auth = \Auth::instance();
			$user_id = $auth->get_user_id();
			$this->u = Model_User::query()->where('id', $user_id[1])->get_one();
		}
		\View::set_global('u', $this->u);
	}
}
/* End of file base.php */
