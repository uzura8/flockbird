<?php
namespace Album;

class Controller_Album extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'member',
		'detail',
		'slide',
		'image_list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Album index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Album list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		list($limit, $page) = $this->common_get_pager_list_params(\Config::get('album.articles.limit'), \Config::get('album.articles.limit_max'));
		$data = Model_Album::get_pager_list(array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list(0, \Auth::check() ? $this->u->id : 0),
			'order_by' => array('id' => 'desc'),
			'limit'    => $limit,
		), $page);
		$this->set_title_and_breadcrumbs(term('site.latest', 'album', 'site.list'));
		$this->template->post_footer = \View::forge('_parts/load_masonry');
		$this->template->content = \View::forge('_parts/list', $data);
	}

	/**
	 * Album member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		$member_id = (int)$member_id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id);

		$this->set_title_and_breadcrumbs(sprintf('%sの%s', $is_mypage ? '自分' : $member->name.'さん', term('album', 'site.list')), null, $member);
		$this->template->subtitle = \View::forge('_parts/member_subtitle', array('member' => $member, 'is_mypage' => $is_mypage));
		$this->template->post_footer = \View::forge('_parts/load_masonry');

		list($limit, $page) = $this->common_get_pager_list_params(\Config::get('album.articles.limit'), \Config::get('album.articles.limit_max'));
		$data = Model_Album::get_pager_list(array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list($member->id, \Auth::check() ? $this->u->id : 0, $this->check_is_mypage($member->id)),
			'order_by' => array('id' => 'desc'),
			'limit'    => $limit,
		), $page);
		$data['member'] = $member;
		$data['is_member_page'] = true;
		$this->template->content = \View::forge('_parts/list', $data);
	}

	/**
	 * Album detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		$id = (int)$id;
		$album = Model_Album::check_authority($id, null, 'member');
		$this->check_browse_authority($album->public_flag, $album->member_id);

		// 既読処理
		if (\Auth::check()) $this->change_notice_status2read($this->u->id, 'album', $id);

		$disabled_to_update = \Album\Site_Util::check_album_disabled_to_update($album->foreign_table);
		list($limit, $page) = $this->common_get_pager_list_params(\Config::get('album.articles.limit'), \Config::get('album.articles.limit_max'));
		$data = Model_AlbumImage::get_pager_list(array(
			'related'  => array('album'),
			'where'    => \Site_Model::get_where_params4list(
				0,
				\Auth::check() ? $this->u->id : 0,
				$this->check_is_mypage($album->member_id),
				array(array('album_id', $id))
			),
			'limit' => $limit,
			'order_by' => array('id' => 'desc'),
		), $page);

		if (\Config::get('album.display_setting.detail.display_upload_form') && !$disabled_to_update && \Auth::check() && $album->member_id == $this->u->id)
		{
			$data['val'] = self::get_validation_public_flag();
		}
		$data['id'] = $id;
		$data['album'] = $album;
		$data['is_member_page'] = true;
		$data['disabled_to_update'] = $disabled_to_update;
		$data['liked_album_image_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
			\Site_Model::get_liked_ids('album_image', $this->u->id, $data['list'], 'Album') : array();

		$this->set_title_and_breadcrumbs($album->name, null, $album->member, 'album');
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album, 'disabled_to_update' => $disabled_to_update));
		$this->template->post_footer = \View::forge('_parts/detail_footer');
		$this->template->content = \View::forge('detail', $data);
	}

	/**
	 * Album upload
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_upload($id = null)
	{
		$id = (int)$id;
		$album = Model_Album::check_authority($id, null, 'member');
		if (Site_Util::check_album_disabled_to_update($album->foreign_table, true))
		{
			throw new \HttpForbiddenException;
		}

		$files = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$file_tmps = array();
			$moved_files = array();
			try
			{
				//if (!$val->run()) throw new \FuelException($val->show_errors());
				$file_tmps = \Site_FileTmp::get_file_tmps_uploaded($this->u->id, true);
				\Site_FileTmp::check_uploaded_under_accepted_filesize($file_tmps, $this->u->filesize_total, \Site_Upload::get_accepted_filesize());

				\DB::start_transaction();
				list($moved_files, $album_image_ids) = \Site_FileTmp::save_images($file_tmps, $album->id, 'album_id', 'album_image', $album->public_flag);
				if (\Module::loaded('timeline')) \Timeline\Site_Model::save_timeline($this->u->id, $album->public_flag, 'album_image', $album->id, null, null, null, $album_image_ids);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				$message = sprintf('%sをアップロードしました。', term('album_image'));
				\Session::set_flash('message', $message);
				\Response::redirect('album/detail/'.$album->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$files = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id);

				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->template->post_header = \View::forge('filetmp/_parts/upload_header');
		$this->template->post_footer = \View::forge('_parts/form/upload_footer');
		$this->set_title_and_breadcrumbs(
			term('album_image', 'form.upload'),
			array('/album/'.$id => $album->name),
			$album->member,
			'album'
		);
		$this->template->content = \View::forge('_parts/form/upload', array('id' => $id, 'album' => $album, 'files' => $files));
	}

	/**
	 * Album slide
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_slide($id = null)
	{
		$album = Model_Album::check_authority($id, null, 'member');
		$disabled_to_update = \Album\Site_Util::check_album_disabled_to_update($album->foreign_table);

		$data = Model_AlbumImage::get_pager_list(array(
			'related'  => array('album'),
			'where'    => \Site_Model::get_where_params4list(
				0,
				\Auth::check() ? $this->u->id : 0,
				$this->check_is_mypage($album->member_id),
				array(array('album_id', $id))
			),
			'order_by' => array('id' => 'desc'),
		));
		$data['album'] = $album;
		$data['disabled_to_update'] = $disabled_to_update;

		$this->set_title_and_breadcrumbs(
			sprintf('%sの%s', $album->name, term('album_image')),
			array('/album/'.$id => $album->name),
			$album->member,
			'album'
		);
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album, 'disabled_to_update' => $disabled_to_update));
		$this->template->post_footer = \View::forge('_parts/slide_footer', array('id' => $id));
		$this->template->content = \View::forge('slide', $data);
	}

	/**
	 * Album create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$album = Model_Album::forge();
		$val = \Validation::forge();
		$val->add_model($album);

		$files = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$file_tmps = array();
			$moved_files = array();
			try
			{
				$file_tmps = \Site_FileTmp::get_file_tmps_uploaded($this->u->id, true);
				\Site_FileTmp::check_uploaded_under_accepted_filesize($file_tmps, $this->u->filesize_total, \Site_Upload::get_accepted_filesize());
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				\DB::start_transaction();
				list($album, $moved_files) = Model_Album::save_with_relations($post, $this->u->id, $album, $file_tmps);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				$message = sprintf('%sを作成しました。', term('album'));
				\Session::set_flash('message', $message);
				\Response::redirect('album/detail/'.$album->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$files = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id);

				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(sprintf('%sを%s', term('album'), term('form.do_create')), null, $this->u, 'album');
		$this->template->post_header = \View::forge('filetmp/_parts/upload_header');
		$this->template->post_footer = \View::forge('_parts/create_footer');
		$this->template->content = \View::forge('_parts/form', array('val' => $val, 'files' => $files));
	}

	/**
	 * Album edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		$album = Model_Album::check_authority($id, $this->u->id, 'member');
		if (Site_Util::check_album_disabled_to_update($album->foreign_table, true))
		{
			throw new \HttpForbiddenException;
		}
		$val = $this->get_validation($album, true);

		if (\Input::method() == 'POST')
		{
			try
			{
				\Util_security::check_csrf();
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				\DB::start_transaction();
				list($album, $moved_files) = Model_Album::save_with_relations($post, $this->u->id, $album);
				\DB::commit_transaction();

				\Session::set_flash('message', term('album').'を編集をしました。');
				\Response::redirect('album/'.$album->id);
			}
			catch(Exception $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(sprintf('%sを%s', term('album'), term('form.do_edit')), array('/album/'.$id => $album->name), $album->member, 'album');
		$this->template->content = \View::forge('_parts/form', array(
			'val' => $val,
			'album' => $album,
			'is_edit' => true,
		));
	}

	/**
	 * Album edit images
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit_images($id = null)
	{
		$album = Model_Album::check_authority($id, $this->u->id, 'member');
		$album_images = Model_AlbumImage::find('all', array(
			'where'    => array(array('album_id' => $id)),
			'order_by' => array('id' => 'asc')
		));
		$is_disabled_to_update_public_flag = Site_Util::check_album_disabled_to_update($album->foreign_table, true);
		$is_enabled_map = is_enabled_map('edit_images', 'album');
		$val = self::get_album_image_validation($is_disabled_to_update_public_flag);

		$shot_at = '';
		$posted_album_image_ids = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$deleted_files = array();
			$post = array();
			try
			{
				$is_delete = \Input::post('clicked_btn') == 'delete';
				$posted_album_image_ids = array_map('intval', \Input::post('album_image_ids', array()));
				if (empty($posted_album_image_ids))
				{
					throw new \FuelException('実施対象が選択されていません');
				}
				if (!\Util_Orm::check_ids_in_models($posted_album_image_ids, $album_images))
				{
					throw new \FuelException('実施対象が正しく選択されていません');
				}

				if (!$is_delete)
				{
					if (!$val->run()) throw new \FuelException($val->show_errors());

					$post = $val->validated();
					if (!strlen($post['name']) && empty($post['shot_at'])
						&& (!$is_disabled_to_update_public_flag && $post['public_flag'] == 99)
						&& ($is_enabled_map && !strlen($post['latitude']) && !strlen($post['longitude'])))
					{
						throw new \FuelException('入力してください');
					}
				}

				\DB::start_transaction();
				if ($is_delete)
				{
					$result = Model_AlbumImage::delete_multiple($posted_album_image_ids);
					$message = $result.'件削除しました';
				}
				else
				{
					$result = Model_AlbumImage::update_multiple_each($posted_album_image_ids, $post, $is_disabled_to_update_public_flag);
					$message = $result.'件更新しました';
				}
				\DB::commit_transaction();

				\Session::set_flash('message', $message);
				\Response::redirect('album/edit_images/'.$id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				$message = $e->getMessage() ?: '更新に失敗しました';
				\Session::set_flash('error', $message);
			}
		}
		$this->set_title_and_breadcrumbs(term('album_image', 'site.management'), array('/album/'.$id => $album->name), $album->member, 'album');
		$this->template->post_header = \View::forge('_parts/datetimepicker_header');
		$this->template->post_footer = \View::forge('_parts/edit_images_footer');
		$data = array(
			'id' => $id, 'album' => $album,
			'album_images' => $album_images,
			'val' => $val,
			'album_image_ids' => $posted_album_image_ids,
			'is_disabled_to_update_public_flag' => $is_disabled_to_update_public_flag,
		);
		$this->template->content = \View::forge('edit_images', $data);
	}

	/**
	 * Album delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_csrf(\Input::get(\Config::get('security.csrf_token_key')));
		$album = Model_Album::check_authority($id, $this->u->id, 'member');
		if (Site_Util::check_album_disabled_to_update($album->foreign_table, true))
		{
			throw new \HttpForbiddenException;
		}

		try
		{
			\DB::start_transaction();
			$album->delete();
			\DB::commit_transaction();
			//if (!empty($deleted_files)) \Site_Upload::remove_files($deleted_files);

			\Session::set_flash('message', term('album').'を削除しました。');
		}
		catch(Exception $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('album/member');
	}

	/**
	 * Album upload image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_upload_image($album_id = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		$album_id = (int)$album_id;
		if (!$album_id || !$album = Model_Album::find($album_id))
		{
			throw new \HttpNotFoundException;
		}
		if (Site_Util::check_album_disabled_to_update($album->foreign_table, true))
		{
			throw new \HttpForbiddenException;
		}

		try
		{
			$val = self::get_validation_public_flag();
			if (!$val->run()) throw new \FuelException($val->show_errors());
			$post = $val->validated();

			\DB::start_transaction();
			list($album_image, $file) = Model_AlbumImage::save_with_relations($album_id, $this->u, $post['public_flag'], null, 'album_image');
			\DB::commit_transaction();
			\Session::set_flash('message', '写真を投稿しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('album/'.$album_id);
	}

	private static function get_validation(Model_Album $album, $is_edit = false)
	{
		$val = \Validation::forge();
		$val->add_model($album);

		$val->add('original_public_flag')
				->add_rule('in_array', \Site_Util::get_public_flags());
		if ($is_edit)
		{
			$val->add('is_update_children_public_flag')
						->add_rule('in_array', array(0, 1));
		}

		return $val;
	}

	private static function get_album_image_validation($is_disabled_to_update_public_flag)
	{
		$val = \Validation::forge();
		$val->add('name', 'タイトル')->add_rule('trim')->add_rule('max_length', 255);
		if (!$is_disabled_to_update_public_flag)
		{
			$options = \Site_Form::get_public_flag_options(null, 'default', true);
			$val->add('public_flag', term('public_flag.label'), array('options' => $options, 'type' => 'radio'))
				->add_rule('required')
				->add_rule('in_array', array_keys($options));
		}
		$val->add('shot_at', '撮影日時')
			->add_rule('trim')
			->add_rule('max_length', 16)
			->add_rule('datetime_except_second')
			->add_rule('datetime_is_past');

		if (is_enabled_map('edit_images', 'album'))
		{
			$val->add('latitude', '緯度')
				->add_rule('numeric_between', -90, 90);

			$val->add('longitude', '経度')
				->add_rule('numeric_between', -180, 180);
		}

		return $val;
	}

	private static function get_validation_public_flag()
	{
		$val = \Validation::forge();
		$options = \Site_Form::get_public_flag_options();
		$val->add('public_flag', term('public_flag.label'), array('options' => $options, 'type' => 'radio'))
			->add_rule('required')
			->add_rule('in_array', array_keys($options));

		return $val;
	}
}
