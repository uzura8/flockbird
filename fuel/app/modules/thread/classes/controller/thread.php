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
		list($limit, $page) = $this->common_get_pager_list_params();
		$data = Site_Model::get_list($limit, $page, get_uid());

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

		// thread_image
		$images = Model_ThreadImage::get4thread_id($thread_id);

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
			'images' => $images,
			'comments' => $list,
			'all_comment_count' => $all_comment_count,
			'comment_next_id' => $next_id,
			'is_liked_self' => $is_liked_self,
			'liked_ids' => (conf('like.isEnabled') && \Auth::check() && $list) ?
				\Site_Model::get_liked_ids('thread_comment', $this->u->id, $list) : array(),
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
		$images = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			$image_tmps = array();
			$moved_images = array();
			$thread_image_ids = array();
			$error_message = '';
			try
			{
				$image_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize($this->u->id, $this->u->filesize_total);
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				\DB::start_transaction();
				$is_changed = $thread->save_with_relations($this->u->id, $post, $image_tmps);
				list($moved_images, $thread_image_ids) = \Site_FileTmp::save_images($image_tmps, $thread->id, 'thread_id', 'thread_image');
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_images);

				$message = sprintf('%sを%sしました。', term('thread'), term('form.create_simple'));
				\Session::set_flash('message', $message);
				\Response::redirect('thread/detail/'.$thread->id);
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Util_Db::get_db_error_message($e);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_images) \Site_FileTmp::move_files_to_tmp_dir($moved_images);
				$images = \Site_FileTmp::get_file_objects($image_tmps, $this->u->id);
				\Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(term('thread').'を書く', null, $this->u, 'thread');
		$this->template->post_header = \View::forge('_parts/form_header');
		$this->template->post_footer = \View::forge('_parts/form_footer');
		$this->template->content = \View::forge('_parts/form', array('val' => $val, 'images' => $images));
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
		$thread_images = \Thread\Model_ThreadImage::get4thread_id($thread->id);
		$images = \Site_Upload::get_file_objects($thread_images, $thread->id);
		$image_tmps = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$moved_images = array();
			$news_image_ids = array();
			$error_message = '';
			try
			{
				$image_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize($this->u->id, $this->u->filesize_total);

				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				\DB::start_transaction();
				$thread->save_with_relations($this->u->id, $post);
				list($moved_images, $thread_image_ids) = \Site_FileTmp::save_images($image_tmps, $thread->id, 'thread_id', 'thread_image');
				\Site_Upload::update_image_objs4file_objects($thread_images, $images);
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_images);

				$message = sprintf('%sを%sしました。', term('thread'), term('form.edit'));
				\Session::set_flash('message', $message);
				\Response::redirect('thread/detail/'.$thread->id);
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Util_Db::get_db_error_message($e);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_images) \Site_FileTmp::move_files_to_tmp_dir($moved_images);
				$image_tmps = \Site_FileTmp::get_file_objects($image_tmps, $this->u->id);
				\Session::set_flash('error', $error_message);
			}
		}
		$images = array_merge($images, $image_tmps);

		$this->set_title_and_breadcrumbs(sprintf('%sを%s', term('thread'), term('form.do_edit')), array('/thread/'.$id => $thread->title), $thread->member, 'thread');
		$this->template->post_header = \View::forge('_parts/form_header');
		$this->template->post_footer = \View::forge('_parts/form_footer');
		$this->template->content = \View::forge('_parts/form', array(
			'val' => $val,
			'thread' => $thread,
			'is_edit' => true,
			'images' => $images,
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
