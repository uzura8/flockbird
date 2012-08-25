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
		Util_security::check_method('POST');
		Util_security::check_csrf();

		try
		{
			$config = array(
				'base_path' => sprintf('img/m/%d', Site_util::get_middle_dir($this->current_user->id)),
				'prefix'    => sprintf('m_%d_', $this->current_user->id),
				'sizes'     => Config::get('site.upload_files.img.m.sizes'),
			);
			if ($this->current_user->get_image()) $config['old_filename'] = $this->current_user->get_image();
			$uploader = new Site_uploader($config);
			$uploaded_file = $uploader->upload();

			DB::start_transaction();
			$file = ($this->current_user->file_id) ? Model_File::find()->where('id', $this->current_user->file_id)->get_one() : new Model_File;
			$file->name = $uploaded_file['new_filename'];
			$file->filesize = $uploaded_file['size'];
			$file->original_filename = $uploaded_file['filename'].'.'.$uploaded_file['extension'];
			$file->type = $uploaded_file['type'];
			$file->member_id = $this->current_user->id;
			$file->save();

			$this->current_user->file_id = $file->id;
			$filesize_total = Model_File::calc_filesize_total($this->current_user->id);
			if ($filesize_total) $this->current_user->filesize_total = $filesize_total;
			$this->current_user->save();
			DB::commit_transaction();

			Session::set_flash('message', '写真を更新しました。');
		}
		catch(Exception $e)
		{
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/setting_image');
	}
}
