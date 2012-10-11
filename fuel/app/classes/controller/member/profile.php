<?php

class Controller_Member_profile extends Controller_Member
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber_profile index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{

		$member = $this->u;
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
		Util_security::check_method('POST');
		Util_security::check_csrf();

		try
		{
			DB::start_transaction();
			$file_id = Site_util::upload('m', $this->u->id, $this->u->id, $this->u->filesize_total, $this->u->get_image(), $this->u->file_id);

			$this->u->file_id = $file_id;
			$this->u->save();
			Model_Member::recalculate_filesize_total($this->u->id);

			DB::commit_transaction();

			Session::set_flash('message', '写真を更新しました。');
		}
		catch(Exception $e)
		{
			\DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/setting_image');
	}

	/**
	 * Mmeber_profile delete_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_delete_image()
	{
		Util_security::check_csrf(Input::get(Config::get('security.csrf_token_key')));

		try
		{
			if (empty($this->u->file_id)) throw new Exception('No profile image.');

			$file_name = $this->u->get_image();
			DB::start_transaction();
			$this->u->filesize_total -= $this->u->file->filesize;
			$this->u->file->delete();
			$this->u->file_id = null;
			$this->u->save();

			Site_util::remove_images('m', $this->u->id, $file_name);
			DB::commit_transaction();

			Session::set_flash('message', '写真を削除しました。');
		}
		catch(Exception $e)
		{
			DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/setting_image');
	}
}
