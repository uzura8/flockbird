<?php

class Controller_Error extends Controller_Site
{
	protected $check_not_auth_action = array(
		'400',
		'403',
		'404',
		'405',
		'500',
		'invalid',
	);

	public function before()
	{
		if (IS_ADMIN) $this->template = 'admin::template';
		parent::before();
	}
	
	/**
	 * The 400 action for the application.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_400()
	{
		$this->set_title_and_breadcrumbs('400 Bad Request', null, null, null, null, true);
		$this->template->content = View::forge('error/common', array('message' => '不正なリクエストです。'));
		$this->response_status = 400;
	}
	
	/**
	 * The 403 action for the application.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_403()
	{
		$this->set_title_and_breadcrumbs('403 Forbidden');
		$this->template->content = View::forge('error/403');
		$this->response_status = 403;
	}
	
	/**
	 * The 404 action for the application.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_404()
	{
		$this->set_title_and_breadcrumbs('404 Not Found', null, null, null, null, true);
		$this->template->content = View::forge('error/404');
		$this->response_status = 404;
	}
	
	/**
	 * The 405 action for the application.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_405()
	{
		$this->set_title_and_breadcrumbs('405 Method Not Allowed', null, null, null, null, true);
		$this->template->content = View::forge('error/405');
		$this->response_status = 405;
	}
	
	/**
	 * The 500 action for the application.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_500()
	{
		$this->set_title_and_breadcrumbs('500 Server Error', null, null, null, null, true);
		$this->template->content = View::forge('error/500');
		$this->response_status = 500;
	}
	
	/**
	 * Error page for invalid input data
	 */
	public function action_invalid()
	{
		$this->set_title_and_breadcrumbs('Invalid input data', null, null, null, null, true);
		$this->template->content = View::forge('error/invalid');
		$this->response_status = 400;
	}
}
