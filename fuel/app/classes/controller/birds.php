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
		$this->set_default_variable4template();
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

	/**
	 * Life_place
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_life_place()
	{
		$this->set_default_variable4template();
		$this->template->breadcrumbs = array(
			'HOME' => '/',
			'鳥から探す' => '/birds/',
			'生活場所から探す' => '',
		);

		$data = array();
		$data['life_places'] = Util_db::get_assoc(\Model\Bplace::get_result_array_all());
		foreach ($data['life_places'] as $key => $value)
		{
			$var_name = 'birds_listP'.$key;
			$data[$var_name] = \Model\Birds::get_result_array4life_place($key, array('name', 'url', 'img'));
		}
		$this->template->content = View::forge('birds/life_place', $data);
	}

	private function set_default_variable4template()
	{
		$this->template->title = '鳥から探す';
		$this->template->header_title = site_title('鳥一覧 特徴、生態、写真');
		$this->template->header_keywords = '野鳥図鑑,ニュース,ブログ,検索';
	}
}
