<?php
namespace Album;

class Controller_Image extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'detail',
	);

	public function before()
	{
		parent::before();
	}

	public function action_index($id = null)
	{
		$this->action_detail();
	}

	public function action_detail($id = null)
	{
		$id = (int)$id;
		if (!$id || !$album_image = Model_Albumimage::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}

		$this->template->title = sprintf(\Config::get('album.term.album_image'), \Config::get('album.term.album_image'));
		if ($album_image->name)
		{
			$this->template->title = $album_image->name;
		}
		elseif ($album_image->file->original_filename)
		{
			$this->template->title = $album_image->file->original_filename;
		}
		$this->template->header_title = site_title($this->template->title);

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if (\Auth::check() && $album_image->album->member_id == $this->u->id)
		{
			$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
			$key = '自分の'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/member/album/';
		}
		else
		{
			$this->template->breadcrumbs[\Config::get('album.term.album')] = '/album/';
			$key = $album_image->album->member->name.'さんの'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/album/list/'.$album_image->album->member->id;
		}
		$this->template->breadcrumbs[$album_image->album->name] =  '/album/detail/'.$album_image->album_id;
		$this->template->breadcrumbs[$this->template->title] = '';

		$data = array('album_image' => $album_image);
		list($data['before_id'], $data['after_id']) =  \Album\Site_util::get_neighboring_album_image_ids($album_image->album_id, $id, 'created_at');

		$this->template->subtitle = \View::forge('image/_parts/detail_subtitle', array('album_image' => $album_image));
		$this->template->post_footer = \View::forge('image/_parts/detail_footer', array('album_image_id' => $album_image->id));
		$this->template->content = \View::forge('image/index.php', $data);
	}

	public function action_create($note_id = null)
	{
		$note_id = (int)$note_id;
		if (!$note_id || !$note = Model_Note::find($note_id))
		{
			throw new \HttpNotFoundException;
		}

		// Lazy validation
		if (\Input::post('body'))
		{
			\Util_security::check_csrf();

			// Create a new comment
			$comment = new Model_NoteComment(array(
				'body' => \Input::post('body'),
				'note_id' => $note_id,
				'member_id' => $this->u->id,
			));

			// Save the post and the comment will save too
			if ($comment->save())
			{
				\Session::set_flash('message', 'コメントしました。');
			}
			else
			{
				\Session::set_flash('error', 'コメントに失敗しました。');
			}

			\Response::redirect('note/detail/'.$note_id);
		}
		else
		{
			Controller_Note::action_detail($note_id);
		}
	}

	/**
	 * Album_image edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		$with_file = (\Input::method() == 'POST')? false : true;
		if (!$album_image = Model_AlbumImage::check_authority($id, $this->u->id, $with_file))
		{
			throw new \HttpNotFoundException;
		}

		$form = $this->form($album_image);

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
					$album_image->name = $post['name'];
					$album_image->save();
					if (!empty($post['shot_at']))
					{
						$file = \Model_File::find($album_image->file_id);
						$file->shot_at = $post['shot_at'].':00';
						$file->save();
					}
					\DB::commit_transaction();

					\Session::set_flash('message', \Config::get('album.term.album_image').'を編集をしました。');
					\Response::redirect('album/image/detail/'.$album_image->id);
				}
				catch(Exception $e)
				{
					\DB::rollback_transaction();
					\Session::set_flash('error', 'Could not save.');
				}
			}
			else
			{
				\Session::set_flash('error', $val->error());
			}
			$form->repopulate();
		}
		else
		{
			$form->populate($album_image);
		}

		$this->template->title = \Config::get('album.term.album_image').'を編集する';
		$this->template->header_title = site_title($this->template->title);
		$this->template->post_header = \View::forge('_parts/edit_header');
		$this->template->post_footer = \View::forge('_parts/edit_footer');

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
		$key = '自分の'.\Config::get('album.term.album').'一覧';
		$this->template->breadcrumbs[$key] =  '/member/album/';
		$key = $album_image->album->name;
		$this->template->breadcrumbs[$key] =  '/album/detail/'.$album_image->album_id;

		$key = sprintf(\Config::get('album.term.album_image'), \Config::get('album.term.album_image'));
		if ($album_image->name)
		{
			$key = $album_image->name;
		}
		elseif ($album_image->file->original_filename)
		{
			$key = $album_image->file->original_filename;
		}
		$this->template->breadcrumbs[$key] = '/album/image/detail/'.$album_image->id;
		$this->template->breadcrumbs[$this->template->title] = '';

		$data = array('form' => $form);
		$this->template->content = \View::forge('edit', $data);
		$this->template->content->set_safe('html_form', $form->build('album/image/edit/'.$id));// form の action に入る
	}

	/**
	 * Album_image delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_csrf(\Input::get(\Config::get('security.csrf_token_key')));
		if (!$album_image = Model_AlbumImage::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}

		$album_id = $album_image->album_id;
		try
		{
			\DB::start_transaction();
			$deleted_filesize = Model_AlbumImage::delete_with_file($id);
			\Model_Member::add_filesize($this->u->id, -$deleted_filesize);
			\DB::commit_transaction();

			\Session::set_flash('message', \Config::get('album.term.album_image').'を削除しました。');
		}
		catch (Exception $e)
		{
			\Session::set_flash('error', $e->getMessage());
			\DB::rollback_transaction();
		}

		\Response::redirect('album/detail/'.$album_id);
	}

	protected function form($album_image = null)
	{
		$form = \Site_util::get_form_instance();

		$form->add('name', \Config::get('album.term.album_image').'タイトル', array('class' => 'input-xlarge'))
			->add_rule('trim')
			->add_rule('max_length', 255);

			$shot_at = '';
			if (\Input::post('shot_at'))
			{
				$shot_at = \Input::post('shot_at');
			}
			elseif (!empty($album_image->file->shot_at))
			{
				$shot_at = substr($album_image->file->shot_at, 0, 16);
			}
			$form->add('shot_at', '撮影日時', array('value' => $shot_at, 'class' => 'input-medium'))
				->add_rule('trim')
				->add_rule('max_length', 16)
				->add_rule('datetime_except_second');

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Util_security::get_csrf()));

		return $form;
	}
}
