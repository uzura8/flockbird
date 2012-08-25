<?php
namespace Album;

class Controller_Image extends \Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();

		$this->auth_check();
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
		$comments = Model_AlbumImageComment::find()->where('album_image_id', $id)->related('member')->order_by('id')->get();

		$this->template->title = sprintf(\Config::get('album.term.album_image'), \Config::get('album.term.album_image'));
		$this->template->header_title = site_title($this->template->title);

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if (\Auth::check() && $album_image->album->member_id == $this->current_user->id)
		{
			$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
			$key = '自分の'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/member/album/';
			$key = $album_image->album->name;
			$this->template->breadcrumbs[$key] =  '/album/detail/'.$album_image->album_id;
		}
		else
		{
			$this->template->breadcrumbs[\Config::get('album.term.album')] = '/album/';
			$key = $album->member->name.'さんの'.\Config::get('album.term.album').'一覧';
			$this->template->breadcrumbs[$key] =  '/album/list/'.$album->member->id;
			$key = $album_image->album->name;
			$this->template->breadcrumbs[$key] =  '/album/detail/'.$album_image->album_id;
		}
		$this->template->breadcrumbs[$this->template->title] = '';

		$this->template->content = \View::forge('image/index.php', array('album_image' => $album_image, 'comments' => $comments));
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
				'member_id' => $this->current_user->id,
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
	 * Note delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_csrf(\Input::get(\Config::get('security.csrf_token_key')));

		if (!$comment = Model_NoteComment::check_authority($id, $this->current_user->id))
		{
			throw new \HttpNotFoundException;
		}

		$note_id = $comment->note_id;
		$comment->delete();

		\Session::set_flash('message', \Config::get('site.term.note').'を削除しました。');
		\Response::redirect('note/detail/'.$note_id);
	}
}
