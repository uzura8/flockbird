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
		$images = array();
		if (Config::get('site.upload.types.img.types.m.save_as_album_image'))
		{
			$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'member');
			$images = \Album\Model_AlbumImage::query()->related('album')->where('album_id', $album_id)->order_by('id', 'desc')->get();
		}

		$this->set_title_and_breadcrumbs(Config::get('term.profile').'写真設定', array('/member/profile/' => Config::get('term.profile')), $this->u);
		$this->template->post_footer = \View::forge('_parts/load_masonry');
		$this->template->content = View::forge('member/profile/setting_image', array('images' => $images));
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
			$file = Site_Member::save_profile_image($this->u);
			DB::commit_transaction();
			Site_Upload::make_thumbnails(
				$file['file_path'],
				$file['filepath'],
				true,
				Config::get('site.upload.types.img.types.m.save_as_album_image') ? 'profile' : null
			);
			Session::set_flash('message', '写真を更新しました。');
		}
		catch(FuelException $e)
		{
			if (DB::in_transaction()) DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/setting_image');
	}

	/**
	 * Mmeber_profile set_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_set_image($album_image_id)
	{
		Util_security::check_csrf();

		try
		{
			if (!Config::get('site.upload.types.img.types.m.save_as_album_image'))
			{
				throw new \HttpNotFoundException;
			}
			if (!$album_image = \Album\Model_AlbumImage::check_authority($album_image_id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			if ($album_image->album->foreign_table != 'member')
			{
				throw new FuelException('Disabled to set album image as profile image.');
			}

			DB::start_transaction();
			$this->u->file_id = $album_image->file_id;
			$this->u->save();

			if ($album_image->album->cover_album_image_id != $album_image->id)
			{
				$album_image->album->cover_album_image_id = $album_image->id;
				$album_image->album->save();
			}
			DB::commit_transaction();

			Session::set_flash('message', Config::get('term.profile').'写真を更新しました。');
		}
		catch(FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/setting_image');

		try
		{
			if (empty($this->u->file_id)) throw new FuelException('No profile image.');

			DB::start_transaction();
			$this->u->file->delete();
			$this->u->file_id = null;
			$this->u->save();
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

	/**
	 * Mmeber_profile delete image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_delete_image($album_image_id = null)
	{
		Util_security::check_csrf();
		$save_as_album_image = Config::get('site.upload.types.img.types.m.save_as_album_image');

		try
		{
			if ($save_as_album_image)
			{
				if ($album_image_id)
				{
					if (!$album_image = \Album\Model_AlbumImage::check_authority($album_image_id, $this->u->id))
					{
						throw new \HttpNotFoundException;
					}
					if ($album_image->album->foreign_table != 'member')
					{
						throw new FuelException('Disabled to set album image as profile image.');
					}
				}
				else
				{
					if (empty($this->u->file_id)) throw new FuelException('No profile image.');
					if (!$album_image = \Album\Model_AlbumImage::query()->related('album')->where('file_id', $this->u->file_id)->get_one())
					{
						throw new FuelException('No profile image.');
					}
				}
			}
			else
			{
				if (empty($this->u->file_id)) throw new FuelException('No profile image.');
			}

			DB::start_transaction();
			if ($save_as_album_image)
			{
				$file_id = $album_image->file_id;
				\Album\Model_AlbumImage::delete_with_file($album_image->id);
				if ($file_id == $this->u->file_id)
				{
					$this->u->file_id = null;
					$this->u->save();
				}
			}
			else
			{
				$file = $this->u->file;
				$this->u->file_id = null;
				$this->u->save();
				\Timeline\Site_Model::delete_timeline('file', $file->id);
				$file->delete();
			}
			DB::commit_transaction();

			Session::set_flash('message', Config::get('term.profile').'写真を削除しました。');
		}
		catch(FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/setting_image');
	}
}
