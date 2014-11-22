<?php

/**
 * The Site Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Site extends Controller_Base_Site
{
	protected $check_not_auth_action = array(
		'index',
	);
	protected $login_val;

	public function before()
	{
		parent::before();
		if (!Auth::check()) $this->set_login_validation();
	}

	protected function set_login_validation()
	{
		$this->login_val = Validation::forge();
		$options = array('1' => '次回から自動的にログイン');
		$this->login_val->add('rememberme', '', array('type' => 'checkbox', 'options' => $options))->add_rule('checkbox_val', $options);
		$this->login_val->add_model(Model_MemberAuth::forge());
		View::set_global('login_val', $this->login_val);
	}

	protected function display_error($message_display = '', $messsage_log = '', $action = 'error/500', $status = 500)
	{
		if ($messsage_log) \Log::error($messsage_log);
		if ($message_display)
		{
			$this->set_title_and_breadcrumbs($message_display);
		}
		$this->template->content = View::forge($action);
		if ($status) $this->response->status = $status;
	}

	/**
	 * Site index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$data = array();
		if (Config::get('page.site.index.timeline.isEnabled') && is_enabled('timeline'))
		{
			$data['timeline'] = \Timeline\Site_Util::get_list4view(
				\Auth::check() ? $this->u->id : 0,
				0, false, null,
				$this->common_get_list_params(array(
					'desc' => 1,
					'latest' => 1,
					'limit' => Config::get('page.site.index.timeline.list.limit'),
				), Config::get('page.site.index.timeline.list.limit_max'), true)
			);
			$this->template->post_footer = \View::forge('timeline::_parts/load_timelines');
		}
		$data['timeline']['see_more_link'] = array('uri' => 'timeline');
		$this->set_title_and_breadcrumbs('', null, null, null, null, true, true);
		$this->template->layout = 'wide';
		$this->template->content = View::forge('site/index', $data);
	}
}
