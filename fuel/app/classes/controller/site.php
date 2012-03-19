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
class Controller_Site extends Controller_Base
{
	public function before()
	{
		parent::before();

		$this->template->header_keywords = '';
		$this->template->header_description = '';
	}

	/**
	 * Site index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = PRJ_SITE_NAME.'メインメニュー';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array('HOME' => '');

		$this->template->content = View::forge('site/index');
	}
}
