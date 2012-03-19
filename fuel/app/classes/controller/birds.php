<?php

/**
 * The Birds Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Birds extends Controller_Template
{
	/**
	 * Index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = '鳥から探す';
		$this->template->header_title = site_title('鳥一覧 特徴、生態、写真');
		$this->template->header_keywords = '野鳥図鑑,ニュース,ブログ,検索';
		$this->template->breadcrumbs = array('HOME' => '/', '鳥から探す' => '');

		$this->template->content = ViewModel::forge('birds/index');
	}
}
