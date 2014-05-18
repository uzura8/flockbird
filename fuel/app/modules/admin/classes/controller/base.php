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

		$this->check_acl();
	}

	protected function check_acl()
	{
		if ($this->check_not_auth_action()) return;
		if (\Auth::has_access(sprintf('%s.%s', site_get_current_page_id(), \Input::method()))) return;

		if (IS_API) return Response::forge(null, 403);

		throw new \HttpForbiddenException;
	}

	protected function get_current_user($user_id)
	{
		return Model_AdminUser::query()->where('id', $user_id)->get_one();
	}

	protected static function get_breadcrumbs($title_name = '', $middle_breadcrumbs = array())
	{
		$breadcrumbs = array('admin' => term('admin.view', 'page.top'));
		if ($middle_breadcrumbs) $breadcrumbs += $middle_breadcrumbs;
		$breadcrumbs[''] = $title_name;

		return $breadcrumbs;
	}
}
/* End of file base.php */
