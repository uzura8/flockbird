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
		$this->set_title_and_breadcrumbs(sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('term.note')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('_parts/member_subtitle') : '';

		$data = \Site_Model::get_simple_pager_list('note', 1, array(
			'where'    => \Site_Model::get_where_params4list($member->id, \Auth::check() ? $this->u->id : 0, $this->check_is_mypage($member_id)),
			'limit'    => \Config::get('note.articles.limit'),
			'order_by' => array('created_at' => 'desc'),
		), 'Note');
		$data['member'] = $member;
		$this->template->content = \View::forge('_parts/list', $data);
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
		if (!$note = Model_Note::check_authority($id)) throw new \HttpNotFoundException;
		$this->check_public_flag($note->public_flag, $note->member_id);

		$images = Model_NoteAlbumImage::get_album_image4note_id($id);

		$record_limit = (\Input::get('all_comment', 0))? 0 : \Config::get('site.record_limit.default.comment.m');
		list($comments, $is_all_records) = Model_NoteComment::get_comments($id, $record_limit);

		$this->set_title_and_breadcrumbs($note->title, null, $note->member, 'note');
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('note' => $note));
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

		$val = \Validation::forge();
		$val->add_model($note);
		$val->add('original_public_flag')
				->add_rule('in_array', \Site_Util::get_public_flags());

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			if ($val->run())
			{
				$post = $val->validated();
				$note->title       = $post['title'];
				$note->body        = $post['body'];
				$note->public_flag = $post['public_flag'];

				if ($note and $note->save())
				{
					\Session::set_flash('message', \Config::get('term.note').'を編集をしました。');
					\Response::redirect('note/detail/'.$note->id);
				}
				else
				{
					\Session::set_flash('error', 'Could not save.');
				}
			}
			else
			{
				\Session::set_flash('error', $val->show_errors());
			}
		}

		$this->set_title_and_breadcrumbs(\Config::get('term.note').'を編集する', array('/note/'.$id => $note->title), $note->member, 'note');
		$this->template->content = \View::forge('_parts/form', array('val' => $val, 'note' => $note));
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
}
