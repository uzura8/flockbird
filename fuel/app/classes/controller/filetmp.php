<?php

class Controller_FileTmp extends Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * FileTmp upload
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_upload()
	{
		$this->template->post_header = \View::forge('filetmp/_parts/upload_header');
		$this->template->post_footer = \View::forge('filetmp/_parts/upload_footer');
		$this->set_title_and_breadcrumbs('ファイルアップロード');
		$this->template->content = View::forge('filetmp/upload', array());
	}
}
