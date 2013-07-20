<?php

class Controller_Error extends Controller_Site
{
	protected $check_not_auth_action = array(
		'403',
		'404',
		'500',
		'invalid',
	);
	
	/**
	 * The 403 action for the application.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_403()
	{
		$this->template->title = '403 Forbidden';
		$this->template->header_title = site_title($this->template->title);
		$this->template->content = View::forge('error/403');
		$this->response->status = 403;
	}
	
	/**
	 * The 404 action for the application.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_404()
	{
		$this->template->title = '404 Not Found';
		$this->template->header_title = site_title($this->template->title);
		$this->template->content = View::forge('error/404');
		$this->response->status = 404;
	}
	
	/**
	 * The 500 action for the application.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_500()
	{
		$this->template->title = '500 Server Error';
		$this->template->header_title = site_title($this->template->title);
		$this->template->content = View::forge('error/500');
		$this->response->status = 500;
	}
	
	/**
	 * Error page for invalid input data
	 */
	public function action_invalid()
	{
		$this->template->title = 'Invalid input data';
		$this->template->header_title = site_title($this->template->title);
		$this->template->content = View::forge('error/invalid');
	}
}
