<?php
namespace Note;

class Controller_Note extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
	}

	/**
	 * Diary index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = \Config::get('site.term.note');
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', \Config::get('site.term.note') => '');

		$this->template->content = \View::forge('index');
	}
}
