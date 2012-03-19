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
class Controller_Birds extends Controller_Site
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

	/**
	 * Detail
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_detail($url)
	{
		$bird = \Model\Birds::get4url($url);

		$this->template->title = $bird['name'].'の特徴と最新ブログ、写真を紹介します。';
		$this->template->header_title = site_title($bird['name'].'の説明、写真、ブログ');
		$this->template->header_description = $bird['name'].'の特徴や写真を紹介し、最新ブログを紹介しています。';
		$this->template->header_keywords = $bird['name'].',ニュース,ブログ,検索,写真';
		$this->template->breadcrumbs = array(
			'HOME' => '/',
			'鳥から探す' => '/birds/',
			$bird['name'] => '',
		);

		$data = array();
		$data['bird'] = $bird;
		$this->template->content = View::forge('birds/detail', $data);
	}
}
