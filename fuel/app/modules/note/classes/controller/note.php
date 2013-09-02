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
			'limit'    => \Config::get('note.articles.limit')
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
		$this->set_title_and_breadcrumbs(sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('term.note')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('_parts/member_subtitle') : '';

		$data = \Site_Model::get_simple_pager_list('note', 1, array(
			'where'    => \Site_Model::get_where_params4list($member->id, \Auth::check() ? $this->u->id : 0, $this->check_is_mypage($member->id)),
			'limit'    => \Config::get('note.articles.limit'),
			'order_by' => array('created_at' => 'desc'),
		), 'Note');
		$data['member'] = $member;
		$this->template->content = \View::forge('_parts/list', $data);
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

		$record_limit = (\Input::get('all_comment', 0))? 0 : \Config::get('site.record_limit.default.comment.m');
		list($comments, $is_all_records) = Model_NoteComment::get_comments($id, $record_limit);

		$this->set_title_and_breadcrumbs($note->title, null, $note->member, 'note');
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('note' => $note));
		$this->template->post_footer = \View::forge('_parts/load_masonry');
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
				$note->title       = $post['title'];
				$note->body        = $post['body'];
				$note->public_flag = $post['public_flag'];
				$note->member_id   = $this->u->id;
				\DB::start_transaction();
				$note->save();
				if ($is_upload['simple'] && \Input::file())
				{
					Model_NoteAlbumImage::save_with_file($note->id, $this->u, $post['public_flag']);
				}
				elseif ($is_upload['multiple'] && $file_tmps)
				{
					$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'note');
					foreach ($file_tmps as $file_tmp)
					{
						$album_image = \Album\Site_Model::move_from_tmp_to_album_image($album_id, $this->u, $file_tmp, $post['public_flag'], false, true);
						// note_album_image の保存
						$note_album_image = Model_NoteAlbumImage::forge();
						$note_album_image->note_id = $note->id;
						$note_album_image->album_image_id = $album_image->id;
						$note_album_image->save();
						\Model_Member::recalculate_filesize_total($this->u->id);
					}
				}
				\DB::commit_transaction();

				\Session::set_flash('message', \Config::get('term.note').'を作成しました。');
				\Response::redirect('note/detail/'.$note->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$tmp_hash = $is_upload['multiple'] ? \Input::get_post('tmp_hash', \Util_toolkit::create_hash()) : '';
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

				\DB::start_transaction();
				$note->save();

				// album_image の update
				$album_images = \Note\Model_NoteAlbumImage::get_album_image4note_id($id);
				$album_images_posted = \Input::post('album_image_id');
				foreach ($album_images as $album_image)
				{
					if (!in_array($album_image->id, $album_images_posted)) continue;

					$album_image->public_flag = $post['public_flag'];
					if (isset($album_image_name_uploaded_posteds[$album_image->id]))
					{
						$album_image->name = trim($album_image_name_uploaded_posteds[$album_image->id]);
					}
					$album_image->save();
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
		\Util_security::check_csrf(\Input::get(\Config::get('security.csrf_token_key')));

		if (!$note = Model_Note::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		$note->delete();

		\Session::set_flash('message', \Config::get('term.note').'を削除しました。');
		\Response::redirect('note/member');
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
