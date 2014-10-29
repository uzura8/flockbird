<?php

class Controller_Member_Profile_Image extends Controller_Member
{
	protected $check_not_auth_action = array(
		'index',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber_Profile_Image index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index($member_id = null)
	{
		list($is_mypage, $member, $access_from) = $this->check_auth_and_is_mypage($member_id);
		$member_profiles = Model_MemberProfile::get4member_id($member->id, true);
		$this->set_title_and_breadcrumbs(term('profile', 'site.picture', 'site.setting'), array('/member/profile/' => term('profile')), $member);

		$images = array();
		if (is_enabled('album') && conf('upload.types.img.types.m.save_as_album_image'))
		{
			$album_id = \Album\Model_Album::get_id_for_foreign_table($member->id, 'member');
			$images = \Album\Model_AlbumImage::query()->related('album')->related('file')->where('album_id', $album_id)->order_by('id', 'desc')->get();
			$this->template->post_footer = \View::forge('_parts/load_masonry');
		}
		$this->template->content = View::forge('member/profile/image/index', array(
			'is_mypage' => $is_mypage,
			'member' => $member,
			'access_from' => $access_from,
			'images' => $images,
			'member_profiles' => $member_profiles
		));
	}

	/**
	 * Mmeber_Profile_Image edit
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_edit()
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();

		try
		{
			DB::start_transaction();
			$file = Site_Member::save_profile_image($this->u);
			DB::commit_transaction();
			Session::set_flash('message', term('site.picture').'を更新しました。');
		}
		catch(FuelException $e)
		{
			if (DB::in_transaction()) DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/image');
	}

	/**
	 * Mmeber_Profile_Image set
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_set($album_image_id)
	{
		Util_security::check_csrf();

		try
		{
			if (!is_enabled('album') || !conf('upload.types.img.types.m.save_as_album_image'))
			{
				throw new \HttpNotFoundException;
			}
			$album_image = \Album\Model_AlbumImage::check_authority($album_image_id, $this->u->id);
			if ($album_image->album->foreign_table != 'member')
			{
				throw new FuelException('Disabled to set album image as profile image.');
			}
			if ($this->u->file_id == $album_image->file_id)
			{
				throw new FuelException('既に設定されています。');
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

			Session::set_flash('message', term('profile', 'site.picture').'を更新しました。');
		}
		catch(FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/image');

		try
		{
			if (empty($this->u->file_id)) throw new FuelException('No profile image.');

			DB::start_transaction();
			$this->u->file->delete();
			$this->u->file_id = null;
			$this->u->save();
			DB::commit_transaction();

			Session::set_flash('message', term('site.picture').'を削除しました。');
		}
		catch(Exception $e)
		{
			DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/image');
	}

	/**
	 * Mmeber_Profile_Image unset
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_unset($album_image_id = null)
	{
		Util_security::check_csrf();

		try
		{
			if (is_enabled('album')) $album_image_id = null;
			if ($album_image_id)
			{
				$album_image = \Album\Model_AlbumImage::check_authority($album_image_id, $this->u->id);
				if ($album_image->album->foreign_table != 'member')
				{
					throw new FuelException('Disabled to set album image as profile image.');
				}
			}
			else
			{
				if (empty($this->u->file_id)) throw new FuelException('No profile image.');
			}

			DB::start_transaction();
			if ($album_image_id)
			{
				$album_image->delete();
			}
			else
			{
				if (is_enabled('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('file', $this->u->file_id);
				$this->u->file_id = null;
				$this->u->save();
			}
			DB::commit_transaction();

			Session::set_flash('message', term('profile', 'site.picture').'を削除しました。');
		}
		catch(FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/image');
	}

	/**
	 * Mmeber_Profile_Image delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_delete($album_image_id = null)
	{
		try
		{
			Util_security::check_csrf();
			if (!is_enabled('album') || !conf('upload.types.img.types.m.save_as_album_image'))
			{
				throw new HttpNotFoundException;
			}
			$album_image = \Album\Model_AlbumImage::check_authority($album_image_id, $this->u->id);
			if ($album_image->album->foreign_table != 'member')
			{
				throw new FuelException('Disabled to set album image as profile image.');
			}

			DB::start_transaction();
			if ($album_image->file_id == $this->u->file_id)
			{
				if (is_enabled('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('file', $this->u->file_id);
				$this->u->file_id = null;
				$this->u->save();
			}
			$album_image->delete();
			DB::commit_transaction();

			Session::set_flash('message', term('profile', 'site.picture').'を削除しました。');
		}
		catch(FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			Session::set_flash('error', $e->getMessage());
		}

		Response::redirect('member/profile/image');
	}
}
