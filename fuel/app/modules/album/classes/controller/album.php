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

		$list = Model_Album::find()->where('member_id', $this->current_user->id)->order_by('created_at', 'desc')->get();
		// paging 未実装, limit数:  Config::get('note.article_list.limit')

		$this->template->subtitle = \View::forge('_parts/member_subtitle');
		$this->template->content = \View::forge('_parts/list', array('member' => $this->current_user, 'list' => $list));
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
		$album_images = Model_AlbumImage::find()->where('album_id', $id)->related('album')->order_by('created_at')->get();

		$this->template->title = trim($album->name);
		$this->template->header_title = site_title(mb_strimwidth($this->template->title, 0, 50, '...'));

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if (\Auth::check() && $album->member_id == $this->current_user->id)
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
		$this->template->post_footer = \View::forge('_parts/footer');
		$this->template->content = \View::forge('detail', array('album' => $album, 'album_images' => $album_images));
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
					'member_id' => $this->current_user->id,
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
		if (!$album = Model_Album::check_authority($id, $this->current_user->id))
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

		if (!$album = Model_Album::check_authority($id, $this->current_user->id))
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
	 * Mmeber_profile edit_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_upload_images($album_id = null)
	{
		if (!$album_id = (int)$album_id || !$album = Model_Album::find($album_id))
		{
			throw new \HttpNotFoundException;
		}

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$config = array(
				'path'   => \Config::get('album.image.album_image.original.path'),
				'prefix' => sprintf('ai_%d_', $album_id),
			);
			\Upload::process($config);

			$error = '';
			if (count(\Upload::get_files()) != 1 || !\Upload::is_valid())
			{
				$result = \Upload::get_errors();
				if (!empty($result[0]['errors'][0]['message'])) $error = $result[0]['errors'][0]['message'];
			}

			if (!$error)
			{
				\Upload::save(0);
				$file = \Upload::get_files(0);
				/**
				 * ここで$fileを使ってアップロード後の処理
				 * $fileの中にいろんな情報が入っている
				 **/

				try
				{
					$this->save_images($file['saved_to'], $file['saved_as'], $album_id);
				}
				catch(Exception $e)
				{
					$error = $e->getMessage();
				}
			}
			if ($error)
			{
				\Session::set_flash('error', $error);
			}
			else
			{
				\Session::set_flash('message', '写真を更新しました。');
			}
		}

		\Response::redirect('album/detail/'.$album_id);
	}

	private function save_images($original_file_dir, $original_file_name, $album_id)
	{
		// 各サイズの icon を作成
		if (!self::make_thumbnails($original_file_dir, $original_file_name))
		{
			throw new Exception('Resize error.');
		}
/*
		$member = $this->current_user;
		// 古い icon の削除
		if (!self::remove_old_images($member->image))
		{
			throw new Exception('Remove old image error.');
		}
*/
		// filename の保存
		$album_image = Model_AlbumImage::forge(array(
			'album_id' => (int)$album_id,
			'image' => $original_file_name,
		));
		$album_image->save();
	}

	private static function make_thumbnails($original_file_dir, $original_file_name)
	{
		$original_file = $original_file_dir.$original_file_name;
		try
		{
			$configs = \Config::get('album.image.album_image');
			foreach ($configs as $key => $config)
			{
				if ($key == 'original') continue;
				\Util_file::resize($original_file, $config['path'].'/'.$original_file_name, $config['width'], $config['height']);
			}
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}

	private static function remove_old_images($old_file_name)
	{
		if (!$old_file_name) return true;

		try
		{
			$configs = \Config::get('album.image.album_image');
			foreach ($configs as $key => $config)
			{
				//if ($key == 'original') continue;
				\Util_file::remove($config['path'].'/'.$old_file_name);
			}
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}
}
