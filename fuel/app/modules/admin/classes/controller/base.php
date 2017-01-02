<?php
namespace Admin;

class Controller_Base extends \Controller_Base
{
	public $template = 'admin::template';
	protected $auth_driver = 'Simpleauth';
	protected $after_auth_uri = 'admin';

	public function before()
	{
		parent::before();
		\Site_Lang::configure_lang(false);
	}

	protected function get_current_user($user_id)
	{
		return Model_AdminUser::query()->where('id', $user_id)->get_one();
	}
}
/* End of file base.php */
