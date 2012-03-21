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

		$data = array();
		$data['subtitle'] = '鳥の名前から探す';
		$data['parent_list'] = self::get_initial_syllabary_list();
		$data['bird_list'] = array();
		foreach ($data['parent_list'] as $key => $value)
		{
			if ($key == 'Y') $value = 'ヤ';
			$value = str_replace('行', '', $value);
			$data['bird_list'][$key] = \Model\Birds::get_result_array4syllabary_range($value, array('name', 'url', 'img'));
		}
		$this->template->content = View::forge('birds/size', $data);
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
		$data['subtitle'] = '鳥の生活場所から探す';
		$data['parent_list'] = Util_db::get_assoc(\Model\BLifePlace::get_result_array_all());
		$data['bird_list'] = self::get_bird_list($data['parent_list'], 'b_life_place_id');
		$this->template->content = View::forge('birds/size', $data);
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
		$data['subtitle'] = '鳥の見られる場所から探す';
		$data['parent_list'] = Util_db::get_assoc(\Model\BWatchSpot::get_result_array_all());
		$data['bird_list'] = self::get_bird_list($data['parent_list'], 'b_watch_spot_id');
		$this->template->content = View::forge('birds/size', $data);
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
		$data['subtitle'] = '鳥のサイズから探す';
		$data['parent_list'] = Util_db::get_assoc(\Model\BSize::get_result_array_all());
		$data['bird_list'] = self::get_bird_list($data['parent_list'], 'b_size_id');
		$this->template->content = View::forge('birds/size', $data);
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
		$data['subtitle'] = '鳥の名前から探す';
		$data['bird'] = $bird;
		$this->template->content = View::forge('birds/detail', $data);
	}

	private function set_default_variable4template()
	{
		$this->template->title = '鳥から探す';
		$this->template->header_title = site_title('鳥一覧 特徴、生態、写真');
		$this->template->header_keywords = '野鳥図鑑,ニュース,ブログ,検索';
	}

	private static function get_initial_syllabary_list()
	{
		return array(
			'A' => 'ア行',
			'K' => 'カ行',
			'S' => 'サ行',
			'T' => 'タ行',
			'N' => 'ナ行',
			'H' => 'ハ行',
			'M' => 'マ行',
			'Y' => 'ヤ・ラ・ワ行',
		);
	}

	private function get_bird_list($parent_list, $key_name)
	{
		$bird_list = array();
		foreach ($parent_list as $key => $value)
		{
			$method = 'get_result_array4'.$key_name;
			$bird_list[$key] = \Model\Birds::$method($key, array('name', 'url', 'img'));
		}

		return $bird_list;
	}
}
