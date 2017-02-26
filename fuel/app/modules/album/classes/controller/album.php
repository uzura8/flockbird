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
		list($limit, $page) = $this->common_get_pager_list_params(conf('articles.limit', 'album'), conf('articles.limit_max', 'album'));
		$data = Site_Model::get_albums($limit, $page, \Auth::check() ? $this->u->id : 0);

		$this->set_title_and_breadcrumbs(term('site.latest', 'album.plural'));
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
		list($limit, $page) = $this->common_get_pager_list_params(conf('articles.limit', 'album'), conf('articles.limit_max', 'album'));
		$data = Site_Model::get_albums($limit, $page, \Auth::check() ? $this->u->id : 0, $member, $is_mypage);

		$title = $is_mypage ? t('common.own_for_myself_of', array('label' => t('album.plural')))
												: t('common.own_for_member_of', array('label' => t('album.plural'), 'name' => $member->name));
		$this->set_title_and_breadcrumbs($title, null, $member, null, null, false, false, null, t('album.plural'));
		$this->template->subtitle = \View::forge('_parts/member_subtitle', array('member' => $member, 'is_mypage' => $is_mypage));
		$this->template->post_footer = \View::forge('_parts/load_masonry');
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
		// 通報リンク
		$this->set_global_for_report_form();

		$disabled_to_update = \Album\Site_Util::check_album_disabled_to_update($album->foreign_table);
		list($limit, $page) = $this->common_get_pager_list_params(\Config::get('articles.limit'), \Config::get('articles.limit_max'));
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

		if (\Config::get('album.display_setting.detail.display_upload_form')
				&& !$disabled_to_update
				&& \Auth::check() && $album->member_id == $this->u->id)
		{
			$data['val'] = self::get_validation_public_flag();
		}
		$data['id'] = $id;
		$data['album'] = $album;
		$data['is_member_page'] = true;
		$data['disabled_to_update'] = $disabled_to_update;
		$data['liked_album_image_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
			\Site_Model::get_liked_ids('album_image', $this->u->id, $data['list']) : array();

		$this->set_title_and_breadcrumbs($album->get_display_name(), null, $album->member, 'album', null, false, false, array(
			'title' => $album->get_display_name(),
			'description' => $album->body,
			'image' => \Site_Util::get_image_uri4image_list($data['list'], 'ai', 'raw'),
		), t('site.detail'));
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album, 'disabled_to_update' => $disabled_to_update));
		$this->template->post_footer = \View::forge('_parts/detail_footer');
		$this->template->post_footer = \View::forge('_parts/detail_footer', array('is_mypage' => check_uid($album->member_id)));
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
		$album = Model_Album::check_authority($id, $this->u->id, 'member');
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

				// Create thumbnails and delete
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				$message = __('message_upload_complete_for', array('label' => t('album.image.view')));
				\Session::set_flash('message', $message);
				$redirect_uri = 'album/detail/'.$album->id;
				if (FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.album.isEnabled') && conf('service.facebook.shareDialog.album.autoPopupAfterUploaded'))
				{
					$redirect_uri .= '?created=1';
				}
				\Response::redirect($redirect_uri);
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
			t('form.upload_for', array('label' => t('album.image.view'))),
			array('/album/'.$id => $album->get_display_name()),
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
		$data = array(
			'content_id' => $id,
			'body' => $album->body,
		);

		$this->set_title_and_breadcrumbs(
			t('common.delimitter.of', array('object' => $album->get_display_name(), 'subject' => t('album.image.plural'))),
			array('/album/'.$id => $album->get_display_name()), $album->member, 'album', null, false, false, null, t('album.image.plural')
		);
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album, 'disabled_to_update' => $disabled_to_update));
		$this->template->post_footer = \View::forge('_parts/slide_footer', array('is_desc' => true));
		$this->template->content = \View::forge('_parts/slide', $data);
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

				// Create thumbnails and delete
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				$message = __('message_create_complete_for', array('label' => t('album.view')));
				\Session::set_flash('message', $message);
				$redirect_uri = 'album/detail/'.$album->id;
				if (FBD_FACEBOOK_APP_ID
					&& conf('service.facebook.shareDialog.album.isEnabled')
					&& conf('service.facebook.shareDialog.album.autoPopupAfterCreated'))
				{
					$redirect_uri .= '?created=1';
				}
				\Response::redirect($redirect_uri);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$files = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id);

				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(__('form.do_create_for', array('label' => t('album.view'))), null, $this->u, 'album');
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

				\Session::set_flash('message', __('message_edit_complete_for', array('label' => t('album.view'))));
				\Response::redirect('album/'.$album->id);
			}
			catch(Exception $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(t('form.do_edit_for', array('label' => t('album.view'))), array(
			'album/'.$id => $album->get_display_name(),
		), $album->member, 'album');
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
					throw new \FuelException(__('message_not_selected_for', array('label' => t('common.exec_targets'))));
				}
				if (!\Util_Orm::check_ids_in_models($posted_album_image_ids, $album_images))
				{
					throw new \FuelException(__('message_invalid_selected_for', array('label' => t('common.exec_targets'))));
				}

				if (!$is_delete)
				{
					if (!$val->run()) throw new \FuelException($val->show_errors());

					$post = $val->validated();
					if (!strlen($post['name']) && empty($post['shot_at'])
						&& (!$is_disabled_to_update_public_flag && $post['public_flag'] == 99)
						&& ($is_enabled_map && !strlen($post['latitude']) && !strlen($post['longitude'])))
					{
						throw new \FuelException(__('message_please_input'));
					}
				}

				\DB::start_transaction();
				if ($is_delete)
				{
					$result = Model_AlbumImage::delete_multiple($posted_album_image_ids);
					$message =__('message_delete_complete_bulk', array('num' => $result));
				}
				else
				{
					$result = Model_AlbumImage::update_multiple_each($posted_album_image_ids, $post, $is_disabled_to_update_public_flag);
					$message =__('message_edit_complete_bulk', array('num' => $result));
				}
				\DB::commit_transaction();

				\Session::set_flash('message', $message);
				\Response::redirect('album/edit_images/'.$id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				$message = $e->getMessage() ?: __('message_update_failed');
				\Session::set_flash('error', $message);
			}
		}
		$this->set_title_and_breadcrumbs(term('album.image.view', 'form.edit_all'), array('album/'.$id => $album->get_display_name()), $album->member, 'album');
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
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
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
			\Session::set_flash('message', __('message_delete_complete_for', array('label' => t('album.view'))));
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
		$album = Model_Album::check_authority($album_id, $this->u->id, 'member');
		if (Site_Util::check_album_disabled_to_update($album->foreign_table, true))
		{
			throw new \HttpForbiddenException;
		}

		try
		{
			$val = self::get_validation_public_flag();
			if (!$val->run()) throw new \ValidationFailedException($val->show_errors());
			$post = $val->validated();

			\DB::start_transaction();
			list($album_image, $file) = Model_AlbumImage::save_with_relations($album_id, $this->u, $post['public_flag'], null, 'album_image');
			\DB::commit_transaction();
			\Session::set_flash('message', __('imessage_upload_complete_for', array('label' => t('album.image.view'))));
		}
		catch(\ValidationFailedException $e)
		{
			\Session::set_flash('error', $e->getMessage());
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
		$val->add('name', t('album.image.name'))->add_rule('trim')->add_rule('max_length', 255);
		if (!$is_disabled_to_update_public_flag)
		{
			$options = \Site_Form::get_public_flag_options(null, 'default', true);
			$val->add('public_flag', t('public_flag.label'), array('options' => $options, 'type' => 'radio'))
				->add_rule('required')
				->add_rule('in_array', array_keys($options));
		}
		$val->add('shot_at', t('site.shot_at'))
			->add_rule('trim')
			->add_rule('max_length', 16)
			->add_rule('datetime_except_second')
			->add_rule('datetime_is_past');

		if (is_enabled_map('edit_images', 'album'))
		{
			$val->add('latitude', t('common.latitude'))
				->add_rule('numeric_between', -90, 90);

			$val->add('longitude', t('common.longitude'))
				->add_rule('numeric_between', -180, 180);
		}

		return $val;
	}

	private static function get_validation_public_flag()
	{
		$val = \Validation::forge();
		$options = \Site_Form::get_public_flag_options();
		$val->add('public_flag', t('public_flag.label'), array('options' => $options, 'type' => 'radio'))
			->add_rule('required')
			->add_rule('in_array', array_keys($options));

		return $val;
	}
}
