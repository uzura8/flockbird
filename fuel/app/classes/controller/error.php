<?php

class Controller_Error extends Controller_Site
{
	
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
	 * Error page for invalid input data
	 */
	public function action_invalid()
	{
		$this->template->title = 'Invalid input data';
		$this->template->header_title = site_title($this->template->title);
		$this->template->content = View::forge('error/invalid');
	}
}
