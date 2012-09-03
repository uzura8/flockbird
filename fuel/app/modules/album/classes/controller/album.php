<?php
namespace Album;

class Controller_Album extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'list_member',
		'detail',
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
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
		$this->template->title = sprintf('最新の%s一覧', \Config::get('album.term.album'));
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', $this->template->title => '');

		$list = Model_Album::find()->related('member')->order_by('created_at', 'desc')->get();
		$this->template->content = \View::forge('_parts/list', array('list' => $list));
	}

	/**
	 * Album member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member()
	{
		$this->template->title = sprintf('自分の%s一覧', \Config::get('album.term.album'));
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(
			\Config::get('site.term.toppage') => '/',
			\Config::get('site.term.myhome') => '/member/',
			$this->template->title => '',
		);

		$list = Model_Album::find()->where('member_id', $this->u->id)->order_by('created_at', 'desc')->get();
		// paging 未実装, limit数:  Config::get('note.article_list.limit')

		$this->template->subtitle = \View::forge('_parts/member_subtitle');
		$this->template->content = \View::forge('_parts/list', array('member' => $this->u, 'list' => $list));
	}

	/**
	 * Album list_member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_list_member($member_id = null)
	{
		if (!$member = \Model_Member::check_authority($member_id))
		{
			throw new \HttpNotFoundException;
		}

		$this->template->title = sprintf('%sさんの%s一覧', $member->name, \Config::get('album.term.album'));
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', $this->template->title => '');

		$list = Model_Note::find()->where('member_id', $member_id)->order_by('created_at', 'desc')->get();

		$this->template->content = \View::forge('_parts/list', array('member' => $member, 'list' => $list));
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
		$album_images = Model_AlbumImage::find()->where('album_id', $id)->related('album')->related('file')->order_by('created_at')->get();

		$this->template->title = trim($album->name);
		$this->template->header_title = site_title(mb_strimwidth($this->template->title, 0, 50, '...'));

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if (\Auth::check() && $album->member_id == $this->u->id)
		{
			$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
			$key = '自分の'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/member/album/';
		}
		else
		{
			$this->template->breadcrumbs[\Config::get('album.term.album')] = '/album/';
			$key = $album->member->name.'さんの'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/album/list/'.$album->member->id;
		}
		$this->template->breadcrumbs[\Config::get('site.term.album').'詳細'] = '';

		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album));
		$this->template->post_footer = \View::forge('_parts/detail_footer');
		$this->template->content = \View::forge('detail', array('id' => $id, 'album' => $album, 'album_images' => $album_images));
		//$this->template->content = \View::forge('detail', array('note' => $note, 'comments' => $comments));
	}

	/**
	 * Album manage images
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_manage_images($id = null)
	{
		$id = (int)$id;
		if (!$id || !$album = Model_Album::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}

		$base_path = sprintf('%s/img/ai/%d', PRJ_UPLOAD_DIRNAME, \Site_util::get_middle_dir($id));
		$base_path_full = PRJ_PUBLIC_DIR.'/'.$base_path;
		$sizes = \Config::get('site.upload_files.img.ai.sizes');
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
		$this->template->header_title = site_title(mb_strimwidth($this->template->title, 0, 50, '...'));

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
		$this->template->breadcrumbs['自分の'.\Config::get('album.term.album').'一覧'] =  '/member/album/';
		$this->template->breadcrumbs[\Config::get('site.term.album').'詳細'] = '/album/detail/'.$id;
		$this->template->breadcrumbs[\Config::get('site.term.album').'写真管理'] = '';

		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album));
		$this->template->post_header = \View::forge('_parts/manage_images_header');
		$this->template->post_footer = \View::forge('_parts/manage_images_footer');
		$this->template->content = \View::forge('manage_images', array('id' => $id, 'album' => $album));
		//$this->template->content = \View::forge('detail', array('note' => $note, 'comments' => $comments));
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
		$album_images = Model_AlbumImage::find()->where('album_id', $id)->related('album')->order_by('created_at')->get();

		$this->template->title = trim($album->name);
		$this->template->header_title = site_title(mb_strimwidth($this->template->title, 0, 50, '...'));

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if (\Auth::check() && $album->member_id == $this->u->id)
		{
			$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
			$key = '自分の'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/member/album/';
		}
		else
		{
			$this->template->breadcrumbs[\Config::get('album.term.album')] = '/album/';
			$key = $album->member->name.'さんの'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/album/list/'.$album->member->id;
		}
		$this->template->breadcrumbs[\Config::get('site.term.album').'詳細'] = '';

		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('album' => $album));
		$this->template->post_footer = \View::forge('_parts/slide_footer', array('id' => $id));
		$this->template->content = \View::forge('slide', array('album' => $album, 'album_images' => $album_images));
		$this->template->subside_contents = \View::forge('_parts/subside_contents');
		//$this->template->content = \View::forge('detail', array('note' => $note, 'comments' => $comments));
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
					'shot_at'  => date('Y-m-d H:i:s'),
				));

				if ($album and $album->save())
				{
					\Session::set_flash('message', \Config::get('album.term.album').'を作成しました。');
					\Response::redirect('album/detail/'.$album->id);
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
			\Config::get('album.term.album') => '/album/',
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
					\Response::redirect('album/detail/'.$album->id);
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
			'自分の'.\Config::get('album.term.album').'一覧' => '/member/album/',
			\Config::get('album.term.album').'詳細' => '/album/detail/'.$id,
			$this->template->title => '',
		);

		$data = array('form' => $form);
		$this->template->content = \View::forge('edit', $data);
		$this->template->content->set_safe('html_form', $form->build('album/edit/'.$id));// form の action に入る
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
		$album->delete();

		\Session::set_flash('message', \Config::get('album.term.album').'を削除しました。');
		\Response::redirect('album/member');
	}

	protected function form()
	{
		$form = \Fieldset::forge('', array('class' => 'form-horizontal'));

		$form->add('name', \Config::get('album.term.album').'名', array('class' => 'input-xlarge'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('max_length', 255);

		$form->add('body', '説明', array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'))
			->add_rule('required');

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Security::fetch_token()));

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
			$file_id = \Site_util::upload('ai', $album_id, $this->u->id);

			$album_image = new Model_AlbumImage;
			$album_image->album_id = $album_id;
			$album_image->file_id = $file_id;
			$album_image->shot_at = date('Y-m-d H:i:s');
			$album_image->save();

			\Model_Member::recalculate_filesize_total($this->u->id);
			\DB::commit_transaction();

			\Session::set_flash('message', '写真を投稿しました。');
		}
		catch(Exception $e)
		{
			\DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('album/detail/'.$album_id);
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
				'sizes'     => Config::get('site.upload_files.img.ai.sizes'),
			);
			if ($this->u->get_image()) $config['old_filename'] = $this->u->get_image();
			$uploader = new Site_uploader($config);
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
		catch(Exception $e)
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

		$config_upload_files = \Config::get('site.upload_files.img.ai');
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
				'max_width' => $width,
				'max_height' => $height,
			);
		}
		$upload_handler = new UploadHandler($options);

		$response = \Request::active()->controller_instance->response;
		$response->set_header('Pragma', 'no-cache');
		$response->set_header('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->set_header('Content-Disposition', 'inline; filename="files.json"');
		$response->set_header('X-Content-Type-Options', 'nosniff');
		$response->set_header('Access-Control-Allow-Origin', '*');
		$response->set_header('Access-Control-Allow-Methods', 'OPTIONS, HEAD, GET, POST, PUT, DELETE');
		$response->set_header('Access-Control-Allow-$response->set_headers', 'X-File-Name, X-File-Type, X-File-Size');

		$body = '';
		switch (\Input::method()) {
			case 'OPTIONS':
				break;
			case 'HEAD':
			case 'GET':
				$body = $upload_handler->get($album_id);
				$response->set_header('Content-type', 'application/json');
				break;
			case 'POST':
				$_method = \Input::post('_method');
				if (isset($_method) && $_method === 'DELETE') {
					$body = $upload_handler->delete();
				}
				else
				{
					$body = $upload_handler->post($album_id, $this->u->id, $config_upload_files['max_size']);
					$HTTP_ACCEPT = \Input::server('HTTP_ACCEPT', null);
					if (isset($HTTP_ACCEPT) && (strpos($HTTP_ACCEPT, 'application/json') !== false))
					{
						$response->set_header('Content-type', 'application/json');
					}
					else
					{
						$response->set_header('Content-type', 'text/plain');
					}
				}
				break;
			case 'DELETE':
				$body = $upload_handler->delete($album_id);
				$response->set_header('Content-type', 'application/json');
				break;
			default:
				header('HTTP/1.1 405 Method Not Allowed');
		}

		return $response->body($body);
	}
}
