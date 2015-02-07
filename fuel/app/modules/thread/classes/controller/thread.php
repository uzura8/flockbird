<?php
namespace Thread;

class Controller_Thread extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'detail',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Thread index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Thread list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		list($limit, $page) = $this->common_get_pager_list_params(conf('view_params_default.list.limit'), conf('view_params_default.list.limit_max'));
		$data = Model_Thread::get_pager_list(array(
			'related'  => 'member',
			'where'    => \Site_Model::get_where_params4list(0, \Auth::check() ? $this->u->id : 0, false, array()),
			'order_by' => array('created_at' => 'desc'),
			'limit'    => $limit,
		), $page);
		$data['liked_thread_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
			\Site_Model::get_liked_ids('thread', $this->u->id, $data['list'], 'Thread') : array();
		$this->set_title_and_breadcrumbs(term('thread', 'site.list'));
		$this->template->content = \View::forge('_parts/list', $data);
		if (IS_AUTH) $this->template->subtitle = \View::forge('_parts/list_subtitle');
		$this->template->post_footer = \View::forge('_parts/list_footer');
	}

	/**
	 * Thread detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		$thread_id = (int)$id;
		$thread = Model_Thread::check_authority($thread_id);
		$this->check_browse_authority($thread->public_flag, $thread->member_id);

		// 既読処理
		if (\Auth::check()) $this->change_notice_status2read($this->u->id, 'thread', $id);

		// note_album_image
		//$images = is_enabled('album') ? Model_NoteAlbumImage::get_album_image4note_id($id) : array();

		// thread_comment
		$default_params = array('latest' => 1);
		list($limit, $is_latest, $is_desc, $since_id, $max_id)
			= $this->common_get_list_params($default_params, conf('view_params_default.detail.comment.limit_max'));
		list($list, $next_id, $all_comment_count)
			= Model_ThreadComment::get_list(array('thread_id' => $thread_id), $limit, $is_latest, $is_desc, $since_id, $max_id, null, false, true);

		// thread_like
		$is_liked_self = \Auth::check() ? Model_ThreadLike::check_liked($id, $this->u->id) : false;

		$title = array('name' => $thread->title);
		$header_info = array();
		$this->set_title_and_breadcrumbs($title, array('thread' => term('thread', 'site.list')), null, 'thread', $header_info);
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('thread' => $thread));
		$this->template->post_footer = \View::forge('_parts/comment/handlebars_template');

		$data = array(
			'thread' => $thread,
			//'images' => $images,
			'comments' => $list,
			'all_comment_count' => $all_comment_count,
			'comment_next_id' => $next_id,
			'is_liked_self' => $is_liked_self,
			'liked_ids' => (conf('like.isEnabled') && \Auth::check() && $list) ?
				\Site_Model::get_liked_ids('thread_comment', $this->u->id, $list, 'Thread') : array(),
		);
		$this->template->content = \View::forge('detail', $data);
	}

	/**
	 * Thread create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$thread = Model_Thread::forge();
		$val = self::get_validation_object($thread);
		$files = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$file_tmps = array();
			$moved_files = array();
			try
			{
				//$file_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize($this->u->id, $this->u->filesize_total);
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				\DB::start_transaction();
				list($is_changed, $moved_files) = $thread->save_with_relations($this->u->id, $post, $file_tmps);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				//\Site_FileTmp::make_and_remove_thumbnails($moved_files, 'thread');

				$message = sprintf('%sを%sしました。', term('thread'), term('form.create_simple'));
				\Session::set_flash('message', $message);
				\Response::redirect('thread/detail/'.$thread->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$files = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id);

				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(term('thread').'を書く', null, $this->u, 'thread');
		$this->template->post_header = \View::forge('_parts/form_header');
		$this->template->post_footer = \View::forge('_parts/form_footer');
		$this->template->content = \View::forge('_parts/form', array('val' => $val, 'files' => $files));
	}

	/**
	 * Thread edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		$thread = Model_Thread::check_authority($id, $this->u->id);
		$val = self::get_validation_object($thread, true);
		$album_images = array();
		//if (is_enabled('album'))
		//{
		//	$album_id = \Album\Model_Album::get_id_for_foreign_table($this->u->id, 'note');
		//	$album_images = Model_NoteAlbumImage::get_album_image4note_id($note->id);
		//}
		//$files = is_enabled('album') ? \Site_Upload::get_file_objects($album_images, $album_id, false, $this->u->id) : array();
		$files = array();

		$file_tmps = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$moved_files = array();
			try
			{
//				if (is_enabled('album')) $file_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize($this->u->id, $this->u->filesize_total);

				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				\DB::start_transaction();
				list($is_changed, $moved_files) = $thread->save_with_relations($this->u->id, $post, $file_tmps, $album_images, $files);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
//				\Site_FileTmp::make_and_remove_thumbnails($moved_files, 'thread');

				$message = sprintf('%sを%sしました。', term('thread'), term('form.edit'));
				\Session::set_flash('message', $message);
				\Response::redirect('thread/detail/'.$thread->id);
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

		$this->set_title_and_breadcrumbs(sprintf('%sを%s', term('thread'), term('form.do_edit')), array('/thread/'.$id => $thread->title), $thread->member, 'thread');
		$this->template->post_header = \View::forge('_parts/form_header');
		$this->template->post_footer = \View::forge('_parts/form_footer');
		$this->template->content = \View::forge('_parts/form', array(
			'val' => $val,
			'thread' => $thread,
			'is_edit' => true,
			'files' => $files,
		));
	}

	/**
	 * Thread delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_csrf();

		try
		{
			\DB::start_transaction();
			$thread = Model_Thread::check_authority($id, $this->u->id);
			$thread->delete();
			\DB::commit_transaction();
			\Session::set_flash('message', term('thread').'を削除しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('thread');
	}

	private static function get_validation_object(Model_Thread $thread, $is_edit = false)
	{
		$val = \Validation::forge();
		$val->add_model($thread);

		if ($is_edit)
		{
			$val->add('original_public_flag')
					->add_rule('in_array', \Site_Util::get_public_flags());
		}

		return $val;
	}
}
