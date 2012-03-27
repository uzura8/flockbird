<?php
namespace Diary;

class Controller_Diary extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'list',
	);

	public function before()
	{
		parent::before();

		if (!$this->check_not_auth_action() && !\Auth::check()) \Response::redirect('site/login');
		if ($this->check_not_auth_action() && \Auth::check()) \Response::redirect('member/index');
	}

	/**
	 * Diary index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = '日記';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', '日記' => '');

		$this->template->content = \View::forge('index');
	}
}
