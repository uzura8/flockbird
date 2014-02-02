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
		$this->set_title_and_breadcrumbs(sprintf('最新の%s一覧', \Config::get('term.album')));
		$this->template->post_footer = \View::forge('_parts/load_masonry');
		$data = \Site_Model::get_simple_pager_list('album', 1, array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list(0, \Auth::check() ? $this->u->id : 0),
			'order_by' => array('created_at' => 'desc'),
			'limit'    => \Config::get('album.articles.limit'),
		), 'Album');
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

		$this->set_title_and_breadcrumbs(sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('term.album')), null, $member);
		$this->template->subtitle = \View::forge('_parts/member_subtitle', array('member' => $member, 'is_mypage' => $is_mypage));
		$this->template->post_footer = \View::forge('_parts/load_masonry');

		$data = \Site_Model::get_simple_pager_list('album', 1, array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list($member->id, \Auth::check() ? $this->u->id : 0, $this->check_is_mypage($member->id)),
			'order_by' => array('created_at' => 'desc'),
			'limit'    => \Config::get('album.articles.limit'),
		), 'Album');
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
		if (!$album = Model_Album::check_authority($id)) throw new \HttpNotFoundException;
		$this->check_public_flag($album->public_flag, $album->member_id);
		$disabled_to_update = \Album\Site_Util::check_album_disabled_to_update($album->foreign_table);

		$data = \Site_Model::get_simple_pager_list('album_image', 1, array(
			'related'  => array('file', 'album'),
			'where'    => \Site_Model::get_where_params4list(
				0,
				\Auth::check() ? $this->u->id : 0,
				$this->check_is_mypage($album->member_id),
				array(array('album_id', $id))
			),
			'order_by' => array('shot_at' => 'asc'),
		), 'Album');

		$data['album_images'] = array();
		if (\Config::get('album.display_setting.detail.display_slide_image'))
		{
			$data['album_images'] = $data['list'];
		}
		if (\Config::get('album.display_setting.detail.display_upload_form') && !$disabled_to_update && \Auth::check() && $album->member_id == $this->u->id)
		{
			$data['val'] = self::get_val_public_flag();
		}
		$data['list'] = array_slice($data['list'], 0, \Config::get('album.articles.limit'));
		$data['id'] = $id;
		$data['album'] = $album;
		$data['is_member_page'] = true;
		$data['disabled_to_update'] = $disabled_to_update;

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
		if (!$id || !$album = Model_Album::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}
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
				list($moved_files, $album_image_ids) = \Site_FileTmp::save_as_album_images($file_tmps, $album->id, $album->public_flag);
				if (\Module::loaded('timeline')) \Timeline\Site_Model::save_timeline($this->u->id, $album->public_flag, 'album_image', $album->id, null, null, $album_image_ids);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				$message = sprintf('%sをアップロードしました。', \Config::get('term.album_image'));
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
		$this->template->post_footer = \View::forge('_parts/upload_footer');
		$this->set_title_and_breadcrumbs(
			\Config::get('term.album_image').'アップロード',
			array('/album/'.$id => $album->name),
			$album->member,
			'album'
		);
		$this->template->content = \View::forge('upload', array('id' => $id, 'album' => $album, 'files' => $files));
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
		if (!$album = Model_Album::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}
		$disabled_to_update = \Album\Site_Util::check_album_disabled_to_update($album->foreign_table);
		$album_images = Model_AlbumImage::find('all', array('where' => array('album_id' => $id), 'order_by_rows' => 'created_at'));

		$data = \Site_Model::get_simple_pager_list('album_image', 1, array(
			'related'  => array('file', 'album'),
			'where'    => \Site_Model::get_where_params4list(
				0,
				\Auth::check() ? $this->u->id : 0,
				$this->check_is_mypage($album->member_id),
				array(array('album_id', $id))
			),
			'order_by' => array('created_at' => 'desc'),
		), 'Album');
		$data['album'] = $album;
		$data['disabled_to_update'] = $disabled_to_update;

		$this->set_title_and_breadcrumbs(
			sprintf('%sの%s', $album->name, \Config::get('term.album_image')),
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
				$album->name = $post['name'];
				$album->body = $post['body'];
				$album->public_flag = $post['public_flag'];
				$album->member_id = $this->u->id;
				$album->save();
				list($moved_files, $album_image_ids) = \Site_FileTmp::save_as_album_images($file_tmps, $album->id, $album->public_flag);
				if (\Module::loaded('timeline')) \Timeline\Site_Model::save_timeline($this->u->id, $post['public_flag'], 'album', $album->id, null, null, $album_image_ids);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				$message = sprintf('%sを作成しました。', \Config::get('term.album'));
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

		$this->set_title_and_breadcrumbs(\Config::get('term.album').'を作成する', null, $this->u, 'album');
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
		if (!$album = Model_Album::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		if (Site_Util::check_album_disabled_to_update($album->foreign_table, true))
		{
			throw new \HttpForbiddenException;
		}

		$val = \Validation::forge();
		$val->add_model($album);
		$val->add('original_public_flag')
				->add_rule('in_array', \Site_Util::get_public_flags());
		$val->add('is_update_children_public_flag')
					->add_rule('in_array', array(0,1));

		if (\Input::method() == 'POST')
		{
			try
			{
				\Util_security::check_csrf();
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				$is_update_public_flag = ($album->public_flag != $post['public_flag']);

				\DB::start_transaction();
				// update album
				$album->name = $post['name'];
				$album->body = $post['body'];
				if ($is_update_public_flag) $album->public_flag = $post['public_flag'];
				$album->save();

				// update album_image public_flag
				if ($is_update_public_flag && !empty($post['is_update_children_public_flag']))
				{
					Model_AlbumImage::update_public_flag4album_id($album->id, $post['public_flag']);
				}
				// timeline の public_flag の更新
				if ($is_update_public_flagif && \Module::loaded('timeline'))
				{
					\Timeline\Model_Timeline::update_public_flag4foreign_table_and_foreign_id($post['public_flag'], 'album', $album->id, \Config::get('timeline.types.album'));
				}
				\DB::commit_transaction();

				\Session::set_flash('message', \Config::get('term.album').'を編集をしました。');
				\Response::redirect('album/'.$album->id);
			}
			catch(Exception $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(\Config::get('term.album').'を編集する', array('/album/'.$id => $album->name), $album->member, 'album');
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
		if (!$album = Model_Album::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		$album_images = Model_AlbumImage::find('all', array(
			'related'  => array('file'),
			'where'    => array(array('album_id' => $id)),
			'order_by' => array('created_at' => 'asc')
		));
		$is_disabled_to_update_public_flag = Site_Util::check_album_disabled_to_update($album->foreign_table, true);

		$val = self::get_val($is_disabled_to_update_public_flag);

		$shot_at = '';
		$posted_album_image_ids = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			$is_delete = \Input::post('clicked_btn') == 'delete';

			$error = '';
			$posted_album_image_ids = array_map('intval', \Input::post('album_image_ids', array()));
			if (empty($posted_album_image_ids))
			{
				$error = '実施対象が選択されていません';
			}
			if (!$error && !\Site_Util::check_ids_in_model_objects($posted_album_image_ids, $album_images))
			{
				$error = '実施対象が正しく選択されていません';
			}

			$post = array();
			if (!$error && !$is_delete)
			{
				if ($val->run())
				{
					$post = $val->validated();
					if (!strlen($post['name']) && empty($post['shot_at']) && (!$is_disabled_to_update_public_flag && $post['public_flag'] == 99))
					{
						$error =  '入力してください';
					}
				}
				else
				{
					$error = $val->show_errors();
				}
			}

			if (!$error)
			{
				$deleted_files = array();
				try
				{
					$result        = 0;
					\DB::start_transaction();
					if ($is_delete)
					{
						list($result, $deleted_files) = Model_AlbumImage::delete_multiple($posted_album_image_ids, $album);
					}
					else
					{
						$result = Model_AlbumImage::update_multiple_each($posted_album_image_ids, $post, $is_disabled_to_update_public_flag);
					}
					$message = $result.'件更新しました';
					\DB::commit_transaction();

					if ($is_delete && !empty($deleted_files)) \Site_Upload::remove_files($deleted_files);

					\Session::set_flash('message', $message);
					\Response::redirect('album/edit_images/'.$id);
				}
				catch(\FuelException $e)
				{
					if (\DB::in_transaction()) \DB::rollback_transaction();
					Session::set_flash('error', '更新に失敗しました');
				}
			}
			if ($error) \Session::set_flash('error', $error);
		}

		$this->set_title_and_breadcrumbs(\Config::get('term.album_image').'管理', array('/album/'.$id => $album->name), $album->member, 'album');
		$this->template->post_header = \View::forge('_parts/date_timepicker_header');
		$this->template->post_footer = \View::forge('_parts/date_timepicker_footer', array('attr' => '#form_shot_at'));

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

		if (!$album = Model_Album::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		if (Site_Util::check_album_disabled_to_update($album->foreign_table, true))
		{
			throw new \HttpForbiddenException;
		}

		try
		{
			\DB::start_transaction();
			$deleted_files = Model_Album::delete_relations($album);
			\DB::commit_transaction();
			if (!empty($deleted_files)) \Site_Upload::remove_files($deleted_files);

			\Session::set_flash('message', \Config::get('term.album').'を削除しました。');
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
	public function action_upload_image()
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		$album_id = (int)\Input::post('id');
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
			$val = self::get_val_public_flag();
			if (!$val->run()) throw new \FuelException($val->show_errors());
			$post = $val->validated();

			\DB::start_transaction();
			list($album_image, $file) = Model_AlbumImage::save_with_file($album_id, $this->u, $post['public_flag']);

			// timeline 投稿
			if (\Module::loaded('timeline')) \Timeline\Site_Model::save_timeline($this->u->id, $post['public_flag'], 'album_image', $album->id, null, null, array($album_image->id));
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

	public function action_edit_image()
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();

		try
		{
			$config = array(
				'base_path' => sprintf('img/m/%d', \Site_Upload::get_middle_dir($this->u->id)),
				'sizes'     => \Config::get('site.upload.types.img.types.ai.sizes'),
				'max_size'  => \Config::get('site.upload.types.img.ai.max_size', \Config::get('site.upload.types.img.defaults.max_size')),
				'max_file_size' => PRJ_UPLOAD_MAX_FILESIZE,
			);
			if ($this->u->get_image()) $config['old_filename'] = $this->u->get_image();
			$uploader = new \Site_Uploader($config);
			$uploaded_file = $uploader->execute();

			\DB::start_transaction();
			$file = ($this->u->file_id) ? \Model_File::find($this->u->file_id) : new \Model_File;
			$file->name = $uploaded_file['new_filename'];
			$file->filesize = $uploaded_file['size'];
			$file->original_filename = $uploaded_file['filename'].'.'.$uploaded_file['extension'];
			$file->type = $uploaded_file['type'];
			$file->member_id = $this->u->id;
			$file->save();
			\Controller_Base_Site::add_member_filesize_total($file->size);
			\DB::commit_transaction();

			\Session::set_flash('message', '写真を更新しました。');
		}
		catch(\Exception $e)
		{
			\DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('member/profile/setting_image');
	}

	private static function get_val($is_disabled_to_update_public_flag)
	{
		$val = \Validation::forge();
		$val->add('name', 'タイトル')->add_rule('trim')->add_rule('max_length', 255);
		if (!$is_disabled_to_update_public_flag)
		{
			$options = \Site_Form::get_public_flag_options();
			$val->add('public_flag', \Config::get('term.public_flag.label'), array('options' => $options, 'type' => 'radio'))
				->add_rule('required')
				->add_rule('in_array', array_keys($options));
		}
		$val->add('shot_at', '撮影日時')
			->add_rule('trim')
			->add_rule('max_length', 16)
			->add_rule('datetime_except_second')
			->add_rule('datetime_is_past');

		return $val;
	}

	private static function get_val_public_flag()
	{
		$val = \Validation::forge();
		$options = \Site_Form::get_public_flag_options();
		$val->add('public_flag', \Config::get('term.public_flag.label'), array('options' => $options, 'type' => 'radio'))
			->add_rule('required')
			->add_rule('in_array', array_keys($options));

		return $val;
	}
}
