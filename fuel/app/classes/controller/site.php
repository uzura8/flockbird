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
		$this->set_title_and_breadcrumbs('', null, null, null, null, true, true);
		$this->template->layout = 'wide';
		$this->template->content = View::forge('site/index');
	}
}
