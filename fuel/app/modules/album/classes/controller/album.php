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
		$data = Site_Album::get_album_list(1);

		$this->template->title = sprintf('最新の%s一覧', \Config::get('album.term.album'));
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', $this->template->title => '');
		$this->template->post_footer = \View::forge('_parts/list_footer');
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

		$is_mypage = false;
		if (!$member_id)
		{
			$this->auth_check();

			$is_mypage = true;
			$member = $this->u;
		}
		elseif (\Auth::check() && $member_id == $this->u->id)
		{
			$is_mypage = true;
			$member = $this->u;
		}
		else
		{
			if (!$member = \Model_Member::check_authority($member_id)) throw new \HttpNotFoundException;
		}

		$this->template->title = sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('album.term.album'));
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if ($is_mypage)
		{
			$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member';
		}
		else
		{
			$key = sprintf('%sさんの%s', $member->name, \Config::get('site.term.profile'));
			$this->template->breadcrumbs[$key] = '/member/'.$member->id;
		}
		$this->template->breadcrumbs[$this->template->title] = '';

		$data = Site_Album::get_album_list(1, $member->id);
		$this->template->subtitle = $is_mypage ? \View::forge('_parts/member_subtitle') : '';
		$this->template->post_footer = \View::forge('_parts/list_footer');
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
		if (!$album = Model_Album::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}

		$this->template->title = trim($album->name);
		$this->template->header_title = site_title($this->template->title);

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if (\Auth::check() && $album->member_id == $this->u->id)
		{
			$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';

			$key = '自分の'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/album/member/';
		}
		else
		{
			$key = sprintf('%sさんの%s', $album->member->name, \Config::get('site.term.profile'));
			$this->template->breadcrumbs[$key] = '/member/'.$album->member->id;

			$key = $album->member->name.'さんの'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/album/member/'.$album->member->id;
		}
		$this->template->breadcrumbs[$this->template->title] = '';

		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album));
		$this->template->post_footer = \View::forge('_parts/detail_footer');

		$data = Site_Album::get_album_image_list($id, 1);
		$data['album'] = $album;
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

		$base_path = sprintf('%s/img/ai/%d', PRJ_UPLOAD_DIRNAME, \Site_util::get_middle_dir($id));
		$base_path_full = PRJ_PUBLIC_DIR.'/'.$base_path;
		$sizes = \Config::get('site.upload_files.img.type.ai.sizes');
		// 保存ディレクトリの確認&作成
		foreach ($sizes as $size)
		{
			$dir = sprintf('%s/%s', $base_path_full, $size);
			if (!file_exists($dir) && $target_path = \Util_file::check_exists_file_path($dir, 4))
			{
				\Util_file::make_dir_recursive($dir);
				\Util_file::chmod_recursive($target_path, 0777);
			}
		}

		$this->template->title = trim($album->name);
		$this->template->header_title = site_title($this->template->title);

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
		$this->template->breadcrumbs['自分の'.\Config::get('album.term.album').'一覧'] =  '/album/member/';
		$this->template->breadcrumbs[$album->name] = '/album/'.$id;
		$this->template->breadcrumbs[\Config::get('site.term.album').'写真をアップロード'] = '';

		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album));
		$this->template->post_header = \View::forge('_parts/upload_header');
		$this->template->post_footer = \View::forge('_parts/upload_footer');
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

		$this->template->title = sprintf('%sの%sを見る', trim($album->name), \Config::get('album.term.album_image'));
		$this->template->header_title = site_title($this->template->title);

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if (\Auth::check() && $album->member_id == $this->u->id)
		{
			$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
			$key = '自分の'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/album/member/';
		}
		else
		{
			$this->template->breadcrumbs[\Config::get('album.term.album')] = '/album/';
			$key = $album->member->name.'さんの'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/album/member/'.$album->member->id;
		}
		$this->template->breadcrumbs[$album->name] =  '/album/'.$id;
		$this->template->breadcrumbs[\Config::get('album.term.album_image').'を見る'] = '';

		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album));
		$this->template->post_footer = \View::forge('_parts/slide_footer', array('id' => $id));
		$this->template->content = \View::forge('slide', array('album' => $album, 'album_images' => $album_images));
	}

	/**
	 * Album create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$form = $this->form();

		if (\Input::method() == 'POST')
		{
			$val = $form->validation();
			if ($val->run())
			{
				\Util_security::check_csrf();

				$post = $val->validated();
				$album = Model_Album::forge(array(
					'name' => $post['name'],
					'body'  => $post['body'],
					'member_id' => $this->u->id,
				));

				if ($album and $album->save())
				{
					\Session::set_flash('message', \Config::get('album.term.album').'を作成しました。');
					\Response::redirect('album/'.$album->id);
				}
				else
				{
					Session::set_flash('error', 'Could not save post.');
				}
			}
			else
			{
				Session::set_flash('error', $val->show_errors());
			}
		}

		$this->template->title = \Config::get('album.term.album')."を書く";
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(
			\Config::get('site.term.toppage') => '/',
			\Config::get('site.term.myhome')  => '/member/',
			'自分の'.\Config::get('album.term.album').'一覧' => '/album/member/',
			$this->template->title => '',
		);
		$data = array('form' => $form);
		$this->template->content = \View::forge('create', $data);
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

		$form = $this->form();

		if (\Input::method() == 'POST')
		{
			$val = $form->validation();
			if ($val->run())
			{
				\Util_security::check_csrf();

				$post = $val->validated();
				$album->name = $post['name'];
				$album->body  = $post['body'];

				if ($album and $album->save())
				{
					\Session::set_flash('message', \Config::get('album.term.album').'を編集をしました。');
					\Response::redirect('album/'.$album->id);
				}
				else
				{
					Session::set_flash('error', 'Could not save.');
				}
			}
			else
			{
				Session::set_flash('error', $val->show_errors());
			}
			$form->repopulate();
		}
		else
		{
			$form->populate($album);
		}

		$this->template->title = \Config::get('album.term.album').'を編集する';
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(
			\Config::get('site.term.toppage') => '/',
			\Config::get('site.term.myhome') => '/member/',
			'自分の'.\Config::get('album.term.album').'一覧' => '/album/member/',
			\Config::get('album.term.album').'詳細' => '/album/'.$id,
			$this->template->title => '',
		);

		$data = array('form' => $form);
		$this->template->content = \View::forge('edit', $data);
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

			$val->add('name', 'タイトル')->add_rule('trim')->add_rule('no_controll')->add_rule('max_length', 255);
			$val->add('shot_at', '撮影日時')
				->add_rule('trim')
				->add_rule('max_length', 16)
				->add_rule('datetime_except_second')
				->add_rule('datetime_is_past');

			$error = '';
			$posted_album_image_ids = array_map('intval', \Input::post('album_image_ids', array()));
			if (empty($posted_album_image_ids))
			{
				$error =  '実施対象が選択されていません';
			}
			if (!$error && !\Site_util::check_ids_in_model_objects($posted_album_image_ids, $album_images))
			{
				$error =  '実施対象が正しく選択されていません';
			}

			$post = array();
			if (!$error && \Input::post('clicked_btn') == 'post')
			{
				if ($val->run())
				{
					$post = $val->validated();
					if (empty($post['name']) && empty($post['shot_at']))
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
				$file_ids = array();
				if (!(\Input::post('post') && empty($post['shot_at'])))
				{
					$file_ids =\Util_db::conv_col(\DB::select('file_id')->from('album_image')->where('id', 'in', $posted_album_image_ids)->execute()->as_array());
				}

				$is_db_error = false;
				$message     = '';
				\DB::start_transaction();
				if (\Input::post('clicked_btn') == 'delete')
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
				elseif (\Input::post('clicked_btn') == 'post')
				{
					$updated_at = date('Y-m-d H:i:s');
					if (!empty($post['name']))
					{
						$values = array('name' => $post['name'], 'updated_at' => $updated_at);
						if (!$result = \DB::update('album_image')->set($values)->where('id', 'in', $posted_album_image_ids)->execute()) $is_db_error = true;
					}
					if (!empty($post['shot_at']))
					{
						$values = array('shot_at' => $post['shot_at'], 'updated_at' => $updated_at);
						if (!$result = \DB::update('file')->set($values)->where('id', 'in', $file_ids)->execute()) $is_db_error = true;
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
					\Session::set_flash('message', $message);
					\Response::redirect('album/edit_images/'.$id);
				}
			}

			if ($error) \Session::set_flash('error', $error);
		}

		$this->template->title = \Config::get('album.term.album_image').'管理';
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(
			\Config::get('site.term.toppage') => '/',
			\Config::get('site.term.myhome')  => '/member/',
			'自分の'.\Config::get('album.term.album').'一覧' => '/album/member/',
			$album->name => '/album/'.$id,
			$this->template->title => '',
		);
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
			\Session::set_flash('message', \Config::get('album.term.album').'を削除しました。');
		}
		catch(Exception $e)
		{
			\DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('album/member');
	}

	protected function form()
	{
		$form = \Site_util::get_form_instance();

		$form->add('name', \Config::get('album.term.album').'名', array('class' => 'input-xlarge'))
			->add_rule('trim')
			->add_rule('no_controll')
			->add_rule('required')
			->add_rule('max_length', 255);

		$form->add('body', '説明', array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'))
			->add_rule('trim')
			->add_rule('no_controll')
			->add_rule('required');

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Util_security::get_csrf()));

		return $form;
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

		try
		{
			\DB::start_transaction();
			$file_id = \Site_util::upload('ai', $album_id, $this->u->id, $this->u->filesize_total);

			$album_image = new Model_AlbumImage;
			$album_image->album_id = $album_id;
			$album_image->file_id = $file_id;
			$album_image->save();

			\Model_Member::recalculate_filesize_total($this->u->id);
			\DB::commit_transaction();

			\Session::set_flash('message', '写真を投稿しました。');
		}
		catch(\Exception $e)
		{
			\DB::rollback_transaction();
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
				'base_path' => sprintf('img/m/%d', Site_util::get_middle_dir($this->u->id)),
				'prefix'    => sprintf('m_%d_', $this->u->id),
				'sizes'     => \Config::get('site.upload_files.img.type.ai.sizes'),
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

		$base_path = sprintf('%s/img/ai/%d', PRJ_UPLOAD_DIRNAME, \Site_util::get_middle_dir($album_id));
		$base_path_full = PRJ_PUBLIC_DIR.'/'.$base_path;
		$base_url = \Uri::create($base_path);

		$options = array();
		$options['script_url'] = \Uri::create('album/upload_images/'.$album_id);
		$options['upload_dir'] = $base_path_full.'/raw/';
		$options['upload_url'] = $base_url.'/raw/';
		$options['max_file_size'] = PRJ_UPLOAD_MAX_FILESIZE;
		$options['max_number_of_files'] = PRJ_MAX_FILE_UPLOADS;

		$config_upload_files = \Config::get('site.upload_files.img.type.ai');
		$sizes = $config_upload_files['sizes'];
		$thumbnail_size = $config_upload_files['thumbnail_size'];
		$options['image_versions'] = array();
		foreach ($sizes as $size)
		{
			if ($size == 'raw') continue;

			$key = ($size == $thumbnail_size)? 'thumbnail' : $size;
			list($width, $height) = explode('x', $size);
			$options['image_versions'][$key] = array(
				'upload_dir' => sprintf('%s/%s/', $base_path_full, $size),
				'upload_url' => sprintf('%s/%s/', $base_url, $size),
				'max_width'  => $width,
				'max_height' => $height,
			);
		}
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
					if (PRJ_IS_LIMIT_UPLOAD_FILE_SIZE)
					{
						$accepted_upload_filesize_type = 'small';// default
						$upload_handler->accepted_upload_filesize = (int)\Util_string::convert2bytes(\Config::get('site.accepted_upload_filesize_type.'.$accepted_upload_filesize_type.'.limit_size'));
						$upload_handler->member_filesize_total    = $this->u->filesize_total;
					}
					$upload_handler->is_save_exif_data = PRJ_USE_EXIF_DATA;
					$body = $upload_handler->post($album_id, $this->u->id, $config_upload_files['max_size']);
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
