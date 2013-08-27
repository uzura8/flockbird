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
			$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'member_profile');

			$sizes = Arr::merge(\Config::get('site.upload.types.img.types.ai.additional_sizes.profile'), \Config::get('site.upload.types.img.types.ai.sizes'));
			$album_image = \Album\Model_AlbumImage::save_with_file($album_id, $this->u, Config::get('site.public_flag.default'), null, null, $sizes);
			$this->u->file_id = $album_image->file->id;
			$this->u->save();
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
			if ($this->u->filesize_total < 0) $this->u->filesize_total = 0;
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
