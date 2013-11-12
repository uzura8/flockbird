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

		$images = Model_NoteAlbumImage::get_album_image4note_id($id);

		$record_limit = \Config::get('site.view_params_default.detail.comment.limit');
		if (\Input::get('all_comment', 0)) $record_limit = \Config::get('site.view_params_default.detail.comment.limit_max');
		list($comments, $is_all_records) = Model_NoteComment::get_comments($id, $record_limit);

		$title = array('name' => $note->title);
		$header_info = array();
		if (!$note->is_published)
		{
			$title['label'] = array('name' => \Config::get('term.draft'), 'attr' => 'label-inverse');
			$header_info = array('title' => sprintf('この%sはまだ公開されていません。',  \Config::get('term.note')), 'body' => '');
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
		$val = \Validation::forge();
		$val->add_model($note);
		$val->add('published_at_time', '日時')
				->add_rule('datetime_except_second')
				->add_rule('datetime_is_past');
		$val->add('is_draft', \Config::get('term.draft'))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array(0,1));

		$is_upload = array();
		$is_upload['simple']   = \Config::get('note.display_setting.form.upload.display') && \Config::get('note.display_setting.form.upload.type') == 'simple';
		$is_upload['multiple'] = \Config::get('note.display_setting.form.upload.display') && \Config::get('note.display_setting.form.upload.type') == 'multiple';
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$file_tmps = array();
			if ($is_upload['multiple'])
			{
				$file_tmps = \Site_Util::check_and_get_posted_file_tmps($this->u->id);
				$this->save_file_tmp_config_posted_album_image_names($file_tmps);
			}

			try
			{
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

				// timeline 投稿
				if ($note->is_published)
				{
					\Timeline\Site_Model::save_timeline($this->u->id, $post['public_flag'], 'note', $note->id);
				}

				if ($is_upload['simple'] && \Input::file())
				{
					Model_NoteAlbumImage::save_with_file($note->id, $this->u, $post['public_flag']);
				}
				elseif ($is_upload['multiple'] && $file_tmps)
				{
					$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'note');
					$album_image_public_flag = $note->is_published ? $post['public_flag'] : PRJ_PUBLIC_FLAG_PRIVATE;
					foreach ($file_tmps as $file_tmp)
					{
						$album_image = \Album\Site_Model::move_from_tmp_to_album_image($album_id, $this->u, $file_tmp, $album_image_public_flag, false, true);
						// note_album_image の保存
						$note_album_image = Model_NoteAlbumImage::forge();
						$note_album_image->note_id = $note->id;
						$note_album_image->album_image_id = $album_image->id;
						$note_album_image->save();
						\Model_Member::recalculate_filesize_total($this->u->id);
					}
				}
				\DB::commit_transaction();

				$message = sprintf('%sを%sしました。', \Config::get('term.note'), $note->is_published ? '作成' : \Config::get('term.draft'));
				\Session::set_flash('message', $message);
				\Response::redirect('note/detail/'.$note->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$tmp_hash = $is_upload['multiple'] ? \Input::get_post('tmp_hash', \Util_toolkit::create_hash()) : '';
		$this->template->post_header = \View::forge('_parts/date_timepicker_header');
		$this->template->post_footer = \View::forge('_parts/date_timepicker_footer', array('attr' => '#form_published_at_time'));
		$this->set_title_and_breadcrumbs(\Config::get('term.note').'を書く', null, $this->u, 'note');
		$this->template->content = \View::forge('_parts/form', array('val' => $val, 'is_upload' => $is_upload, 'tmp_hash' => $tmp_hash));
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
		$is_upload = array();
		$is_upload['simple']   = \Config::get('note.display_setting.form.upload.display') && \Config::get('note.display_setting.form.upload.type') == 'simple';
		$is_upload['multiple'] = \Config::get('note.display_setting.form.upload.display') && \Config::get('note.display_setting.form.upload.type') == 'multiple';

		$val = \Validation::forge();
		$val->add_model($note);
		$val->add('original_public_flag')
				->add_rule('in_array', \Site_Util::get_public_flags());
		$val->add('published_at_time', '日時')
				->add_rule('datetime_except_second')
				->add_rule('datetime_is_past');
		if (!$note->is_published)
		{
			$val->add('is_draft', \Config::get('term.draft'))
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', array(0,1));
		}

		$album_image_name_uploaded_posteds = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$file_tmps = array();
			if ($is_upload['multiple'])
			{
				$file_tmps = \Site_Util::check_and_get_posted_file_tmps($this->u->id);
				$this->save_file_tmp_config_posted_album_image_names($file_tmps);
				$album_image_name_uploaded_posteds = \Input::post('album_image_name_uploaded');
			}

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());

				$post = $val->validated();
				$note->title       = $post['title'];
				$note->body        = $post['body'];
				$note->public_flag = $post['public_flag'];

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

				// album_image の update
				if ($album_images_posted = \Input::post('album_image_id'))
				{
					$album_images = \Note\Model_NoteAlbumImage::get_album_image4note_id($id);
					$album_image_public_flag = $note->is_published ? $post['public_flag'] : PRJ_PUBLIC_FLAG_PRIVATE;
					foreach ($album_images as $album_image)
					{
						if (!in_array($album_image->id, $album_images_posted)) continue;

						$album_image->public_flag = $album_image_public_flag;
						if (isset($album_image_name_uploaded_posteds[$album_image->id]))
						{
							$album_image->name = trim($album_image_name_uploaded_posteds[$album_image->id]);
						}
						$album_image->save();
					}
				}

				// tmp_file を album_image として保存
				if ($is_upload['simple'] && \Input::file())
				{
					Model_NoteAlbumImage::save_with_file($note->id, $this->u, $post['public_flag']);
				}
				elseif ($is_upload['multiple'] && $file_tmps)
				{
					$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'note');
					foreach ($file_tmps as $file_tmp)
					{
						$album_image = \Album\Site_Model::move_from_tmp_to_album_image($album_id, $this->u, $file_tmp, $post['public_flag']);
						// note_album_image の保存
						$note_album_image = Model_NoteAlbumImage::forge();
						$note_album_image->note_id = $note->id;
						$note_album_image->album_image_id = $album_image->id;
						$note_album_image->save();
						\Model_Member::recalculate_filesize_total($this->u->id);
					}
				}
				// timeline 投稿
				if ($is_published)
				{
					\Timeline\Site_Model::save_timeline($this->u->id, $note->public_flag, 'note', $note->id);
				}
				\DB::commit_transaction();

				\Session::set_flash('message', \Config::get('term.note').'を編集しました。');
				\Response::redirect('note/detail/'.$note->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$tmp_hash = $is_upload['multiple'] ? \Input::get_post('tmp_hash', \Util_toolkit::create_hash()) : '';
		$this->set_title_and_breadcrumbs(\Config::get('term.note').'を編集する', array('/note/'.$id => $note->title), $note->member, 'note');
		$this->template->post_header = \View::forge('_parts/date_timepicker_header');
		$this->template->post_footer = \View::forge('_parts/date_timepicker_footer', array('attr' => '#form_published_at_time'));
		$this->template->content = \View::forge('_parts/form', array(
			'val' => $val,
			'note' => $note,
			'is_upload' => $is_upload,
			'tmp_hash' => $tmp_hash,
			'is_edit' => true,
			'album_image_name_uploaded_posteds' => $album_image_name_uploaded_posteds,
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
			\Timeline\Site_Model::delete_timeline('note', $note->id);
			$note->delete_with_images();
			\DB::commit_transaction();
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
			$album_images = \Note\Model_NoteAlbumImage::get_album_image4note_id($id);
			foreach ($album_images as $album_image)
			{
				$album_image->public_flag = $note->public_flag;
				$album_image->save();
			}
			// timeline 投稿
			\Timeline\Site_Model::save_timeline($this->u->id, $note->public_flag, 'note', $note->id);
			\DB::commit_transaction();
			\Session::set_flash('message', \Config::get('term.note').'を公開しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('note/detail/'.$id);
	}

	private function save_file_tmp_config_posted_album_image_names($file_tmps)
	{
		$album_image_names = \Input::post('album_image_name');
		foreach ($file_tmps as $file_tmp)
		{
			if (!isset($album_image_names[$file_tmp->id])) continue;
			$value = trim($album_image_names[$file_tmp->id]);
			\Model_FileTmpConfig::update_for_name($file_tmp->id, 'album_image_name', $value);
		}
	}
}
