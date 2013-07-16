<?php
namespace Album;

class Controller_Image extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'detail',
		'member',
	);

	public function before()
	{
		parent::before();
	}

	public function action_index($id = null)
	{
		$this->action_list();
	}

	/**
	 * Album image list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$this->set_title_and_breadcrumbs(sprintf('%s一覧', \Config::get('album.term.album_image')), array('album' => sprintf('%s一覧', \Config::get('album.term.album'))));
		$this->template->post_footer = \View::forge('_parts/list_footer');

		$data = \Site_Model::get_simple_pager_list('album_image', 1, array(
			'related' => array('file', 'album'),
			'limit' => \Config::get('album.articles.limit'),
			'order_by' => array('created_at' => 'desc'),
		), 'Album');
		$this->template->content = \View::forge('image/_parts/list', $data);
	}

	/**
	 * Album image detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		$id = (int)$id;
		if (!$id || !$album_image = Model_Albumimage::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}
		$record_limit = (\Input::get('all_comment', 0))? 0 : \Config::get('site.record_limit.default.comment.m');
		list($comments, $is_all_records) = Model_AlbumImageComment::get_comments($id, $record_limit);

		$title = Site_Util::get_album_image_page_title($album_image->name, $album_image->file->original_filename);
		$this->set_title_and_breadcrumbs($title, array('/album/'.$album_image->album_id => $album_image->album->name), $album_image->album->member, 'album');
		$this->template->subtitle = \View::forge('image/_parts/detail_subtitle', array('album_image' => $album_image));

		$data = array('album_image' => $album_image, 'comments' => $comments, 'is_all_records' => $is_all_records);
		list($data['before_id'], $data['after_id']) =  Site_Util::get_neighboring_album_image_ids($album_image->album_id, $id, 'created_at');
		$this->template->content = \View::forge('image/detail', $data);
	}

	/**
	 * Album image member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		$member_id = (int)$member_id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id);

		$this->set_title_and_breadcrumbs(sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('album.term.album_image')), null, $member);
		$this->template->subtitle = \View::forge('_parts/member_subtitle', array('member' => $member, 'is_mypage' => $is_mypage));
		$this->template->post_footer = \View::forge('_parts/list_footer');

		$data = \Site_Model::get_simple_pager_list('album_image', 1, array(
			'related' => array('file', 'album'),
			'where' => array('t2.member_id', $member_id),
			'limit' => \Config::get('album.articles.limit'),
			'order_by' => array('created_at' => 'desc'),
		), 'Album');
		$data['member'] = $member;
		$this->template->content = \View::forge('image/_parts/list', $data);
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

			$post = array();
			$error = '';
			$val = $form->validation();
			if ($val->run())
			{
				$post = $val->validated();
				if (empty($post['name']) && empty($post['shot_at'])) $error = '入力してください';
			}
			else
			{
				$error = $val->show_errors();
			}

			if (!$error)
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
					\Response::redirect('album/image/'.$album_image->id);
				}
				catch(Exception $e)
				{
					\DB::rollback_transaction();
					\Session::set_flash('error', 'Could not save.');
				}
			}
			else
			{
				\Session::set_flash('error', $error);
			}
			$form->repopulate();
		}
		else
		{
			$form->populate($album_image);
		}

		$album_image_page_title = Site_Util::get_album_image_page_title($album_image->name, $album_image->file->original_filename);
		$this->set_title_and_breadcrumbs(
			\Config::get('album.term.album_image').'を編集する',
			array('/album/'.$album_image->album_id => $album_image->album->name, '/album/image/'.$id => $album_image_page_title),
			$album_image->album->member,
			'album'
		);
		$this->template->post_header = \View::forge('_parts/edit_header');
		$this->template->post_footer = \View::forge('_parts/edit_footer');

		$this->template->content = \View::forge('edit', array('form' => $form));
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

		\Response::redirect('album/'.$album_id);
	}

	protected function form($album_image)
	{
		$form = \Site_Util::get_form_instance('album_image', $album_image);

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
			->add_rule('datetime_except_second')
			->add_rule('datetime_is_past');
		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));

		return $form;
	}
}
