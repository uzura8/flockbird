<?php

class Controller_Member_profile extends Controller_Member
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
	}

	/**
	 * Mmeber_profile index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{

		$member = $this->current_user;
		$this->template->title = $member->name.' さんのページ';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			Config::get('site.term.profile') => '',
		);

		$this->template->content = View::forge('member/profile/index');
	}

	/**
	 * Mmeber_profile setting_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_setting_image()
	{
		$this->template->title = Config::get('site.term.profile').'写真設定';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			Config::get('site.term.profile') => '/member/profile/',
			Config::get('site.term.profile').'写真設定' => '',
		);

		$this->template->content = View::forge('member/profile/setting_image');
	}

	/**
	 * Mmeber_profile edit_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_edit_image()
	{
		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();

			$base_dir = Util_site::get_upload_basedir('img', 'member', $this->current_user->id).'/profile';
			$config = array(
				'path'   => $base_dir.'/raw/',
				'prefix' => sprintf('m_%d_', $this->current_user->id),
			);
			$uploader = new Site_uploader($config);
			$uploader->base_dir = $base_dir;
			if (!$uploader->upload($this->current_user->id, \Config::get('site.upload_files.img.profile.sizes'), $this->current_user))
			{
				Session::set_flash('error', $error);
			}
			else
			{
				Session::set_flash('message', '写真を更新しました。');
			}
		}

		Response::redirect('member/profile/setting_image');
	}
}
