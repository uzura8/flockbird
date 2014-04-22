<?php
namespace Note;

class Controller_Note extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'member',
		'detail',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Note index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Note list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$this->set_title_and_breadcrumbs(sprintf('最新の%s一覧', \Config::get('term.note')));
		$data = \Site_Model::get_simple_pager_list('note', 1, array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list(0, \Auth::check() ? $this->u->id : 0),
			'order_by' => array('created_at' => 'desc'),
			'limit'    => \Config::get('site.view_params_default.list.limit'),
		), 'Note');
		$this->template->content = \View::forge('_parts/list', $data);
		$this->template->post_footer = \View::forge('_parts/load_item');
	}

	/**
	 * Note member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		$member_id = (int)$member_id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id);
		$is_draft = $is_mypage ? \Util_string::cast_bool_int(\Input::get('is_draft', 0)) : 0;
		$is_published = \Util_toolkit::reverse_bool($is_draft, true);

		$this->set_title_and_breadcrumbs(sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('term.note')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('_parts/member_subtitle') : '';
		$data = \Site_Model::get_simple_pager_list('note', 1, array(
			'where'    => \Site_Model::get_where_params4list(
				$member->id,
				\Auth::check() ? $this->u->id : 0,
				$is_mypage,
				null,
				array(array('is_published', $is_published))
			),
			'limit'    => \Config::get('site.view_params_default.list.limit'),
			'order_by' => array('created_at' => 'desc'),
		), 'Note');
		$data['member']       = $member;
		$data['is_mypage']    = $is_mypage;
		$data['is_draft']     = $is_draft;
		$this->template->content = \View::forge('member', $data);
		$this->template->post_footer = \View::forge('_parts/load_item');
	}

	/**
	 * Note detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		if (!$note = Model_Note::check_authority($id)) throw new \HttpNotFoundException;
		$this->check_public_flag($note->public_flag, $note->member_id);

		$images = is_enabled('album') ? Model_NoteAlbumImage::get_album_image4note_id($id) : array();

		$record_limit = \Config::get('site.view_params_default.detail.comment.limit');
		if (\Input::get('all_comment', 0)) $record_limit = \Config::get('site.view_params_default.detail.comment.limit_max');
		list($comments, $is_all_records) = Model_NoteComment::get_comments($id, $record_limit);

		$title = array('name' => $note->title);
		$header_info = array();
		if (!$note->is_published)
		{
			$title['label'] = array('name' => \Config::get('term.draft'));
			$header_info = array('body' => sprintf('この%sはまだ公開されていません。',  \Config::get('term.note')));
		}
		$this->set_title_and_breadcrumbs($title, null, $note->member, 'note', $header_info);
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('note' => $note));
		$this->template->post_footer = \View::forge('_parts/load_masonry', array('is_not_load_more' => true));
		$this->template->content = \View::forge('detail', array('note' => $note, 'images' => $images, 'comments' => $comments, 'is_all_records' => $is_all_records));
	}

	/**
	 * Note create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$note = Model_Note::forge();
		$val = self::get_validation_object($note);
		$files = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$file_tmps = array();
			$moved_files = array();
			try
			{
				$file_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize($this->u->id, $this->u->filesize_total);
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				$note->title        = $post['title'];
				$note->body         = $post['body'];
				$note->public_flag  = $post['public_flag'];
				$note->member_id    = $this->u->id;
				$note->is_published = $post['is_draft'] ? 0 : 1;
				if ($post['published_at_time'])
				{
					$note->published_at = $post['published_at_time'].':00';
				}
				elseif ($note->is_published)
				{
					$note->published_at = date('Y-m-d H:i:s');
				}
				\DB::start_transaction();
				$note->save();
				if (is_enabled('album'))
				{
					$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'note');
					list($moved_files, $album_image_ids) = \Site_FileTmp::save_images($file_tmps, $album_id, 'album_id', 'album_image', 'Album', $note->public_flag);
					\Note\Model_NoteAlbumImage::save_multiple($note->id, $album_image_ids);
				}

				// timeline 投稿
				if ($note->is_published && is_enabled('timeline'))
				{
					\Timeline\Site_Model::save_timeline($this->u->id, $post['public_flag'], 'note', $note->id);
				}
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files, 'note');

				$message = sprintf('%sを%sしました。', \Config::get('term.note'), $note->is_published ? '作成' : \Config::get('term.draft'));
				\Session::set_flash('message', $message);
				\Response::redirect('note/detail/'.$note->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$files = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id);

				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(\Config::get('term.note').'を書く', null, $this->u, 'note');
		$this->template->post_header = \View::forge('_parts/form_header');
		$this->template->post_footer = \View::forge('_parts/form_footer');
		$this->template->content = \View::forge('_parts/form', array('val' => $val, 'files' => $files));
	}

	/**
	 * Note edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		if (!$note = Model_Note::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}

		$val = self::get_validation_object($note, true);
		if (is_enabled('album'))
		{
			$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'note');
			$album_images = Model_NoteAlbumImage::get_album_image4note_id($note->id);
		}
		$files = is_enabled('album') ? \Site_Upload::get_file_objects($album_images, $album_id, false, $this->u->id) : array();

		$file_tmps = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$moved_files = array();
			try
			{
				if (is_enabled('album')) $file_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize($this->u->id, $this->u->filesize_total);

				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				$is_update_public_flag = ($note->public_flag != $post['public_flag']);

				$note->title = $post['title'];
				$note->body  = $post['body'];
				if ($is_update_public_flag) $note->public_flag = $post['public_flag'];

				$is_published = !$note->is_published && empty($post['is_draft']);
				if ($is_published) $note->is_published = 1;

				if ($post['published_at_time'] && !\Util_Date::check_is_same_minute($post['published_at_time'], $note->published_at))
				{
					$note->published_at = $post['published_at_time'].':00';
				}
				elseif ($is_published)
				{
					$note->published_at = date('Y-m-d H:i:s');
				}

				\DB::start_transaction();
				$note->save();
				if (is_enabled('album'))
				{
					list($moved_files, $album_image_ids) = \Site_FileTmp::save_images($file_tmps, $album_id, 'album_id', 'album_image', 'Album', $note->public_flag);
					\Note\Model_NoteAlbumImage::save_multiple($note->id, $album_image_ids);
					\Site_Upload::update_image_objs4file_objects($album_images, $files, $note->public_flag);
				}

				// timeline 投稿
				if (is_enabled('timeline'))
				{
					if ($is_published)
					{
						\Timeline\Site_Model::save_timeline($this->u->id, $note->public_flag, 'note', $note->id);
					}
					elseif ($is_update_public_flag)
					{
						// timeline の public_flag の更新
						\Timeline\Model_Timeline::update_public_flag4foreign_table_and_foreign_id($note->public_flag, 'note', $note->id, \Config::get('timeline.types.note'));
					}
				}
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files, 'note');

				\Session::set_flash('message', \Config::get('term.note').'を編集しました。');
				\Response::redirect('note/detail/'.$note->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$file_tmps = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id);

				\Session::set_flash('error', $e->getMessage());
			}
		}
		$files += $file_tmps;

		$this->set_title_and_breadcrumbs(\Config::get('term.note').'を編集する', array('/note/'.$id => $note->title), $note->member, 'note');
		$this->template->post_header = \View::forge('_parts/form_header');
		$this->template->post_footer = \View::forge('_parts/form_footer');
		$this->template->content = \View::forge('_parts/form', array(
			'val' => $val,
			'note' => $note,
			'is_edit' => true,
			'files' => $files,
		));
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
		\Util_security::check_csrf();

		if (!$note = Model_Note::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		try
		{
			\DB::start_transaction();
			if (is_enabled('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('note', $note->id);
			$deleted_files = $note->delete_with_relations();
			\DB::commit_transaction();
			if (!empty($deleted_files)) \Site_Upload::remove_files($deleted_files);
			\Session::set_flash('message', \Config::get('term.note').'を削除しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('note/member');
	}

	/**
	 * Note publish
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_publish($id = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		if (!$note = Model_Note::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		if ($note->is_published)
		{
			\Session::set_flash('error', '既に公開されています。');
			\Response::redirect('note/detail/'.$id);
		}

		try
		{
			\DB::start_transaction();
			$note->is_published = 1;
			if (!$note->published_at) $note->published_at = date('Y-m-d H:i:s');
			$note->save();

			// album_image の public_flag を update
			if (is_enabled('album'))
			{
				$album_images = \Note\Model_NoteAlbumImage::get_album_image4note_id($id);
				foreach ($album_images as $album_image)
				{
					$album_image->public_flag = $note->public_flag;
					$album_image->save();
				}
			}
			// timeline 投稿
			if (is_enabled('timeline')) \Timeline\Site_Model::save_timeline($this->u->id, $note->public_flag, 'note', $note->id);
			\DB::commit_transaction();
			\Session::set_flash('message', term('note').'を公開しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('note/detail/'.$id);
	}

	private static function get_validation_object(Model_Note $note, $is_edit = false)
	{
		$val = \Validation::forge();
		$val->add_model($note);

		$val->add('published_at_time', '日時')
				->add_rule('datetime_except_second')
				->add_rule('datetime_is_past');
		if (empty($note->is_published))
		{
			$val->add('is_draft', \Config::get('term.draft'))
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', array(0,1));
		}
		if ($is_edit)
		{
			$val->add('original_public_flag')
					->add_rule('in_array', \Site_Util::get_public_flags());
		}

		return $val;
	}
}
