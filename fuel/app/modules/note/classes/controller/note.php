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
		list($limit, $page) = $this->common_get_pager_list_params();
		$data = Site_Model::get_list($limit, $page, get_uid());

		$this->set_title_and_breadcrumbs(term('site.latest', 'note', 'site.list'));
		$this->template->content = \View::forge('_parts/list', $data);
		$this->template->post_footer = \View::forge('_parts/list_footer');
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
		list($limit, $page) = $this->common_get_pager_list_params();
		$data = Site_Model::get_list($limit, $page, \Auth::check() ? $this->u->id : 0, $member, $is_mypage, $is_draft);

		$this->set_title_and_breadcrumbs(sprintf('%sの%s', $is_mypage ? '自分' : $member->name.'さん', term('note', 'site.list')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('_parts/member_subtitle') : '';
		$this->template->content = \View::forge('member', $data);
		$this->template->post_footer = \View::forge('_parts/list_footer');
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
		$note_id = (int)$id;
		$note = Model_Note::check_authority($note_id);
		$this->check_browse_authority($note->public_flag, $note->member_id);

		// 既読処理
		if (\Auth::check()) $this->change_notice_status2read($this->u->id, 'note', $id);

		// note_album_image
		$images = is_enabled('album') ? Model_NoteAlbumImage::get_album_image4note_id($id) : array();

		// note_comment
		$default_params = array('latest' => 1);
		list($limit, $is_latest, $is_desc, $since_id, $max_id)
			= $this->common_get_list_params($default_params, conf('view_params_default.detail.comment.limit_max'));
		list($list, $next_id, $all_comment_count)
			= Model_NoteComment::get_list(array('note_id' => $note_id), $limit, $is_latest, $is_desc, $since_id, $max_id, null, false, true);

		// note_like
		$is_liked_self = \Auth::check() ? Model_NoteLike::check_liked($id, $this->u->id) : false;

		$title = array('name' => $note->title);
		$header_info = array();
		if (!$note->is_published)
		{
			$title['label'] = array('name' => term('form.draft'));
			$header_info = array('body' => sprintf('この%sはまだ公開されていません。',  term('note')));
		}
		$this->set_title_and_breadcrumbs($title, null, $note->member, 'note', $header_info, false, false, array(
			'title' => $note->title,
			'description' => $note->body,
			'image' => \Site_Util::get_image_uri4image_list($images, 'ai'),
		));
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('note' => $note));
		$this->template->post_footer = \View::forge('_parts/detail_footer', array('is_mypage' => check_uid($note->member_id)));
		$data = array(
			'note' => $note,
			'images' => $images,
			'comments' => $list,
			'all_comment_count' => $all_comment_count,
			'comment_next_id' => $next_id,
			'is_liked_self' => $is_liked_self,
			'liked_ids' => (conf('like.isEnabled') && \Auth::check() && $list) ?
				\Site_Model::get_liked_ids('note_comment', $this->u->id, $list) : array(),
		);
		$this->template->content = \View::forge('detail', $data);
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

				\DB::start_transaction();
				list($is_changed, $is_published, $moved_files) = $note->save_with_relations($this->u->id, $post, $file_tmps);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files, 'note');

				$message = sprintf('%sを%sしました。', term('note'), $is_published ? term('form.create_simple') : term('form.draft'));
				\Session::set_flash('message', $message);
				$redirect_uri = 'note/detail/'.$note->id;
				if ($is_published && FBD_FACEBOOK_APP_ID && conf('service.facebook.shareDialog.note.isEnabled') && conf('service.facebook.shareDialog.note.autoPopupAfterCreated'))
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

		$this->set_title_and_breadcrumbs(term('note').'を書く', null, $this->u, 'note');
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
		$note = Model_Note::check_authority($id, $this->u->id);
		$val = self::get_validation_object($note, true);
		$album_images = array();
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

				\DB::start_transaction();
				list($is_changed, $is_published, $moved_files) = $note->save_with_relations($this->u->id, $post, $file_tmps, $album_images, $files);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files, 'note');

				$message = sprintf('%sを%sしました。', term('note'), $is_published ? term('form.publish') : term('form.edit'));
				\Session::set_flash('message', $message);
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
		$files = array_merge($files, $file_tmps);

		$this->set_title_and_breadcrumbs(sprintf('%sを%s', term('note'), term('form.do_edit')), array('/note/'.$id => $note->title), $note->member, 'note');
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
		\Util_security::check_method('POST');
		\Util_security::check_csrf();

		try
		{
			\DB::start_transaction();
			$note = Model_Note::check_authority($id, $this->u->id);
			$note->delete_with_relations();
			\DB::commit_transaction();
			\Session::set_flash('message', term('note').'を削除しました。');
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
		$note = Model_Note::check_authority($id, $this->u->id);
		if ($note->is_published)
		{
			\Session::set_flash('error', '既に公開されています。');
			\Response::redirect('note/detail/'.$id);
		}

		try
		{
			\DB::start_transaction();
			list($is_changed, $is_published) = $note->save_with_relations($this->u->id, array('is_published' => 1));
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
			$val->add('is_draft', term('form.draft'))
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
