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
		$this->template->title = '鳥から探す　アイウエオ順';
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
	 * Lifeplace
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_life_place()
	{
		$this->set_default_variable4template();
		$this->template->title = '鳥から探す　生活場所';
		$this->template->breadcrumbs = array(
			'HOME' => '/',
			'鳥から探す' => '/birds/',
			'生活場所から探す' => '',
		);

		$data = array();
		$data['life_places'] = Util_db::get_assoc(\Model\BLifePlace::get_result_array_all());
		foreach ($data['life_places'] as $key => $value)
		{
			$var_name = 'birds_listP'.$key;
			$data[$var_name] = \Model\Birds::get_result_array4b_life_place_id($key, array('name', 'url', 'img'));
		}
		$this->template->content = View::forge('birds/life_place', $data);
	}

	/**
	 * Wspot
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_watch_spot()
	{
		$this->set_default_variable4template();
		$this->template->title = '鳥から探す　見られる場所';
		$this->template->breadcrumbs = array(
			'HOME' => '/',
			'鳥から探す' => '/birds/',
			'見られる場所から探す' => '',
		);

		$data = array();
		$data['watch_spots'] = Util_db::get_assoc(\Model\BWatchSpot::get_result_array_all());
		foreach ($data['watch_spots'] as $key => $value)
		{
			$var_name = 'birds_listP'.$key;
			$data[$var_name] = \Model\Birds::get_result_array4b_watch_spot_id($key, array('name', 'url', 'img'));
		}
		$this->template->content = View::forge('birds/watch_spot', $data);
	}

	/**
	 * Size
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_size()
	{
		$this->set_default_variable4template();
		$this->template->title = '鳥から探す　サイズ';
		$this->template->breadcrumbs = array(
			'HOME' => '/',
			'鳥から探す' => '/birds/',
			'大きさから探す' => '',
		);

		$data = array();
		$data['sizes'] = Util_db::get_assoc(\Model\BSize::get_result_array_all());
		foreach ($data['sizes'] as $key => $value)
		{
			$var_name = 'birds_listP'.$key;
			$data[$var_name] = \Model\Birds::get_result_array4b_size_id($key, array('name', 'url', 'img'));
		}
		$this->template->content = View::forge('birds/size', $data);
	}

	private function set_default_variable4template()
	{
		$this->template->title = '鳥から探す';
		$this->template->header_title = site_title('鳥一覧 特徴、生態、写真');
		$this->template->header_keywords = '野鳥図鑑,ニュース,ブログ,検索';
	}
}
