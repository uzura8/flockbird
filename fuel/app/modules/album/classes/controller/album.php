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
		$this->template->post_footer = \View::forge('_parts/list_footer');
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
		$this->template->post_footer = \View::forge('_parts/list_footer');

		$data = \Site_Model::get_simple_pager_list('album', 1, array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list($member->id, \Auth::check() ? $this->u->id : 0, $this->check_is_mypage($member_id)),
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

		$this->set_title_and_breadcrumbs($album->name, null, $album->member, 'album');
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album));
		$this->template->post_footer = \View::forge('_parts/detail_footer');

		$data = \Site_Model::get_simple_pager_list('album_image', 1, array(
			'related'  => array('file', 'album'),
			'where'    => \Site_Model::get_where_params4list(
				0,
				\Auth::check() ? $this->u->id : 0,
				$this->check_is_mypage($album->member_id),
				null,
				array(array('album_id', $id))
			),
			'order_by' => array('created_at' => 'desc'),
		), 'Album');

		$data['album_images'] = array();
		if (\Config::get('album.display_setting.detail.display_slide_image'))
		{
			$data['album_images'] = $data['list'];
		}
		$data['list'] = array_slice($data['list'], 0, \Config::get('album.articles.limit'));
		$data['id'] = $id;
		$data['album'] = $album;
		$data['is_member_page'] = true;

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

		\Site_Upload::setup_uploaded_dir('ai', $album->id);

		$this->template->post_header = \View::forge('_parts/upload_header');
		$this->template->post_footer = \View::forge('_parts/upload_footer', array('display_delete_button' => \Config::get('album.display_setting.upload.display_delete_button')));
		$this->template->content = \View::forge('upload', array('id' => $id, 'album' => $album));
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
		$album_images = Model_AlbumImage::find('all', array('where' => array('album_id' => $id), 'order_by_rows' => 'created_at'));

		$data = \Site_Model::get_simple_pager_list('album_image', 1, array(
			'related'  => array('file', 'album'),
			'where'    => \Site_Model::get_where_params4list(
				0,
				\Auth::check() ? $this->u->id : 0,
				$this->check_is_mypage($album->member_id),
				null,
				array(array('album_id', $id))
			),
			'order_by' => array('created_at' => 'desc'),
		), 'Album');
		$data['album'] = $album;

		$this->set_title_and_breadcrumbs(
			sprintf('%sの%s', $album->name, \Config::get('term.album_image')),
			array('/album/'.$id => $album->name),
			$album->member,
			'album'
		);
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album));
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
		$form = \Site_Util::get_form_instance('album', $album, true, array(), 'submit');
		if (\Input::method() == 'POST')
		{
			$val = $form->validation();
			if ($val->run())
			{
				\Util_security::check_csrf();

				$post = $val->validated();
				$album->name = $post['name'];
				$album->body = $post['body'];
				$album->public_flag = $post['public_flag'];
				$album->member_id = $this->u->id;

				if ($album and $album->save())
				{
					\Session::set_flash('message', \Config::get('term.album_image').'を追加してください。');
					\Response::redirect('album/upload/'.$album->id);
				}
				else
				{
					\Session::set_flash('error', 'Could not save post.');
				}
			}
			else
			{
				\Session::set_flash('error', $val->show_errors());
			}
		}
		$this->set_title_and_breadcrumbs(\Config::get('term.album').'を書く', null, $this->u, 'album');

		$form->populate($album, true);
		$this->template->content = \View::forge('create', array('form' => $form));
		$this->template->content->set_safe('html_form', $form->build('album/create'));// form の action に入る
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

		$add_fields = array(
			'original_public_flag' => array(
				'attributes' => array('type' => 'hidden', 'value' => $album->public_flag, 'id' => 'original_public_flag'),
				'rules' => array(array('in_array', \Site_Util::get_public_flags())),
			),
			'is_update_children_public_flag' => array(
				'attributes' => array('type' => 'hidden', 'value' => 0, 'id' => 'is_update_children_public_flag'),
				'rules' => array(array('in_array', array(0, 1))),
			),
		);
		$form = \Site_Util::get_form_instance('album', $album, true, $add_fields, 'button');

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$val = $form->validation();
			if ($val->run())
			{
				try
				{
					$post = $val->validated();

					\DB::start_transaction();
					// update album
					$album->name = $post['name'];
					$album->body = $post['body'];
					$album->public_flag = $post['public_flag'];
					$album->save();

					// update album_image public_flag
					if (!empty($post['is_update_children_public_flag']))
					{
						Model_AlbumImage::update_public_flag4album_id($album->id, $post['public_flag']);
					}
					\DB::commit_transaction();

					\Session::set_flash('message', \Config::get('term.album').'を編集をしました。');
					\Response::redirect('album/'.$album->id);
				}
				catch(Exception $e)
				{
					\DB::rollback_transaction();
					\Session::set_flash('error', 'Could not save.');
				}
			}
			else
			{
				\Session::set_flash('error', $val->show_errors());
			}
			$form->repopulate();
		}
		else
		{
			$form->populate($album);
		}

		$this->set_title_and_breadcrumbs(\Config::get('term.album').'を編集する', array('/album/'.$id => $album->name), $album->member, 'album');
		$this->template->content = \View::forge('edit', array('form' => $form, 'original_public_flag' => $album->public_flag));
		$this->template->content->set_safe('html_form', $form->build('album/edit/'.$id));// form の action に入る
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

		$val = \Validation::forge();

		$shot_at = '';
		$posted_album_image_ids = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			$is_delete = \Input::post('clicked_btn') == 'delete';

			$val->add('name', 'タイトル')->add_rule('trim')->add_rule('max_length', 255);

			$options = \Site_Util::get_public_flags();
			$options[] = 99;
			$val->add('public_flag', \Config::get('term.public_flag.label'), array('options' => $options, 'type' => 'radio'))
				->add_rule('required')
				->add_rule('in_array', $options);

			$val->add('shot_at', '撮影日時')
				->add_rule('trim')
				->add_rule('max_length', 16)
				->add_rule('datetime_except_second')
				->add_rule('datetime_is_past');

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
					if (!strlen($post['name']) && !strlen($post['shot_at']) && $post['public_flag'] == 99)
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
				$file_ids   = array();
				$deleted_files = array();
				if ($is_delete || (!$is_delete && strlen($post['shot_at'])))
				{
					$file_ids = \Util_db::conv_col(\DB::select('file_id')->from('album_image')->where('id', 'in', $posted_album_image_ids)->execute()->as_array());
					if ($is_delete)
					{
						$deleted_files = \DB::select('path', 'name')->from('file')->where('id', 'in', $file_ids)->execute()->as_array();
					}
				}

				$is_db_error = false;
				$message     = '';
				\DB::start_transaction();
				if ($is_delete)
				{
					// カバー写真が削除された場合の対応
					if ($album->cover_album_image_id && in_array($album->cover_album_image_id, $posted_album_image_ids))
					{
						$album->cover_album_image_id = null;
						$album->save();
					}
					if (!$result = \DB::delete('file')->where('id', 'in', $file_ids)->execute()) $is_db_error = true;
					if (!$result = \DB::delete('album_image')->where('id', 'in', $posted_album_image_ids)->execute()) $is_db_error = true;

					\Model_Member::recalculate_filesize_total($this->u->id);

					$message = $result.'件削除しました';
				}
				else
				{
					$updated_at = date('Y-m-d H:i:s');
					$values = array();
					$values['name'] = strlen($post['name']) ? $post['name'] : null;
					if ($post['public_flag'] != 99) $values['public_flag'] = $post['public_flag'];
					if ($post['shot_at']) $values['shot_at'] = $post['shot_at'];
					if (!empty($values))
					{
						$values['updated_at'] = $updated_at;
						if (!$result = \DB::update('album_image')->set($values)->where('id', 'in', $posted_album_image_ids)->execute()) $is_db_error = true;
					}
					$message = $result.'件更新しました';
				}
				if ($is_db_error)
				{
					\DB::rollback_transaction();
					\Session::set_flash('error', '更新に失敗しました');
				}
				else
				{
					\DB::commit_transaction();
					if ($is_delete && !empty($deleted_files))
					{
						foreach ($deleted_files as $deleted_file) \Site_Upload::remove_images($deleted_file['path'], $deleted_file['name']);
					}

					\Session::set_flash('message', $message);
					\Response::redirect('album/edit_images/'.$id);
				}
			}
			if ($error) \Session::set_flash('error', $error);
		}

		$this->set_title_and_breadcrumbs(\Config::get('term.album_image').'管理', array('/album/'.$id => $album->name), $album->member, 'album');
		$this->template->post_header = \View::forge('_parts/edit_header');
		$this->template->post_footer = \View::forge('_parts/edit_footer');

		$data = array('id' => $id, 'album' => $album, 'album_images' => $album_images, 'val' => $val, 'album_image_ids' => $posted_album_image_ids);
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

		try
		{
			\DB::start_transaction();
			Model_Album::delete_all($id);
			\Model_Member::recalculate_filesize_total($this->u->id);
			\DB::commit_transaction();
			\Session::set_flash('message', \Config::get('term.album').'を削除しました。');
		}
		catch(Exception $e)
		{
			\DB::rollback_transaction();
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
		$album_id = (int)\Input::post('id');
		if (!$album_id || !$album = Model_Album::find($album_id))
		{
			throw new \HttpNotFoundException;
		}
		\Util_security::check_csrf();

		$is_start_transaction = false;
		try
		{
			$val = \Validation::forge();
			$options = \Site_Util::get_public_flags();
			$options[] = 99;
			$val->add('public_flag', \Config::get('term.public_flag.label'), array('options' => $options, 'type' => 'radio'))
				->add_rule('required')
				->add_rule('in_array', $options);
			if (!$val->run())
			{
				throw new \FuelException($val->show_errors());
			}
			$post = $val->validated();
			\DB::start_transaction();
			Model_AlbumImage::save_with_file($album_id, $this->u, $post['public_flag']);
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
			$uploader = new \Site_uploader($config);
			$uploaded_file = $uploader->upload();

			\DB::start_transaction();
			$file = ($this->u->file_id) ? \Model_File::find()->where('id', $this->u->file_id)->get_one() : new \Model_File;
			$file->name = $uploaded_file['new_filename'];
			$file->filesize = $uploaded_file['size'];
			$file->original_filename = $uploaded_file['filename'].'.'.$uploaded_file['extension'];
			$file->type = $uploaded_file['type'];
			$file->member_id = $this->u->id;
			$file->save();

			$this->u->file_id = $file->id;
			$this->u->save();
			\Model_Member::recalculate_filesize_total($this->u->id);

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

	/**
	 * Album upload images
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_upload_images($album_id = null)
	{
		$album_id = (int)$album_id;
		if (!$album_id || !$album = Model_Album::find($album_id))
		{
			throw new \HttpNotFoundException;
		}
		//\Util_security::check_csrf();

		$file_cate = 'ai';
		$filepath = \Site_Upload::get_filepath($file_cate, $album_id);
		$script_url = \Uri::create('album/upload_images/'.$album_id);
		$options = \Site_Upload::get_upload_handler_options($filepath, $script_url, $file_cate, 'tmp_hash_upload');
		$upload_handler = new UploadHandler($options);

		$this->response->set_header('Pragma', 'no-cache');
		$this->response->set_header('Cache-Control', 'no-store, no-cache, must-revalidate');
		$this->response->set_header('Content-Disposition', 'inline; filename="files.json"');
		$this->response->set_header('X-Content-Type-Options', 'nosniff');
		$this->response->set_header('Access-Control-Allow-Origin', '*');
		$this->response->set_header('Access-Control-Allow-Methods', 'OPTIONS, HEAD, GET, POST, PUT, DELETE');
		$this->response->set_header('Access-Control-Allow-$response->set_headers', 'X-File-Name, X-File-Type, X-File-Size');

		$body = '';
		switch (\Input::method()) {
			case 'OPTIONS':
				break;
			case 'HEAD':
			case 'GET':
				$body = $upload_handler->get($album_id);
				$this->response->set_header('Content-type', 'application/json');
				break;
			case 'POST':
				$_method = \Input::post('_method');
				if (isset($_method) && $_method === 'DELETE') {
					$body = $upload_handler->delete($this->u->id);
				}
				else
				{
					\Site_Upload::setup_uploaded_dir($file_cate, $album_id, $options['is_tmp']);
					if (PRJ_IS_LIMIT_UPLOAD_FILE_SIZE)
					{
						$accepted_upload_filesize_type = 'small';// default
						$upload_handler->accepted_upload_filesize = (int)\Util_string::convert2bytes(\Config::get('site.upload.accepted_filesize.'.$accepted_upload_filesize_type.'.limit'));
						$upload_handler->member_filesize_total    = $this->u->filesize_total;
					}
					$upload_handler->is_save_exif_data = PRJ_USE_EXIF_DATA;
					$body = $upload_handler->post($album_id, $this->u->id, $options['max_size']);
					$HTTP_ACCEPT = \Input::server('HTTP_ACCEPT', null);
					if (isset($HTTP_ACCEPT) && (strpos($HTTP_ACCEPT, 'application/json') !== false))
					{
						$this->response->set_header('Content-type', 'application/json');
					}
					else
					{
						$this->response->set_header('Content-type', 'text/plain');
					}
				}
				break;
			case 'DELETE':
				$body = $upload_handler->delete($this->u->id);
				$this->response->set_header('Content-type', 'application/json');
				break;
			default:
				header('HTTP/1.1 405 Method Not Allowed');
		}

		return $this->response->body($body);
	}
}
