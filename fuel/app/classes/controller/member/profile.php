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
		$this->set_title_and_breadcrumbs(Config::get('term.profile'), null, $this->u);
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
		$this->set_title_and_breadcrumbs(Config::get('term.profile').'写真設定', array('/member/profile/' => Config::get('term.profile')), $this->u);
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
			$file = Site_Upload::upload('m', $this->u->id, $this->u->id, $this->u->filesize_total, $this->u->get_image(), $this->u->file_id);

			$this->u->file_id = $file->id;
			$this->u->save();
			Model_Member::add_filesize($this->u->id, $file->filesize);

			DB::commit_transaction();

			Session::set_flash('message', '写真を更新しました。');
		}
		catch(FuelException $e)
		{
			DB::rollback_transaction();
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
			if (empty($this->u->file_id)) throw new FuelException('No profile image.');

			DB::start_transaction();
			$this->u->filesize_total -= $this->u->file->filesize;
			$this->u->file->delete();
			$this->u->file_id = null;
			$this->u->save();

			list($filepath, $filename) = Site_Upload::split_file_object2vars($this->u->file);
			Site_Upload::remove_images($filepath, $filename);
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
