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

	public function before()
	{
		parent::before();

		$this->auth_check();
		$this->set_current_user();
	}

	protected function display_error($message_display = '', $messsage_log = '', $action = 'error/500', $status = 500)
	{
		if ($messsage_log) \Log::error($messsage_log);
		if ($message_display)
		{
			$this->template->title = $message_display;
			$this->template->header_title = site_title($this->template->title);
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
		$this->set_title_and_breadcrumbs(PRJ_SITE_NAME.'メインメニュー', null, null, null, null, true);
		$this->template->content = View::forge('site/index');
	}
}
