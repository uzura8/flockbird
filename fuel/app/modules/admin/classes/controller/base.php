<?php
namespace Admin;

class Controller_Base extends \Controller_Base
{
	public $template = 'admin::template';
	protected $auth_driver = 'SimpleAuth';

	public function before()
	{
		parent::before();
	}

	protected function get_current_user($user_id)
	{
		return Model_User::query()->where('id', $user_id)->get_one();
	}
}
/* End of file base.php */
