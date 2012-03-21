<?php

class Controller_Member extends Controller_Site {

	//public $template = 'admin/template';

	public function before()
	{
		parent::before();

		if (!Auth::check()) Response::redirect('site/login');
	}

	/**
	 * Mmeber index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = PRJ_SITE_NAME.'マイホーム';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array('HOME' => '');

		$this->template->content = View::forge('member/index');
	}
}
