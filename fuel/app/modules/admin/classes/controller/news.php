<?php
namespace Admin;

class Controller_News extends Controller_Admin
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * The index action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{		
		$is_draft = \Input::get('is_draft', 0);
		$is_published = \Util_toolkit::reverse_bool($is_draft, true);

		$data = array();
		$data['is_draft'] = $is_draft;

		$query = \News\Model_News::query();
		$config = array(
			'uri_segment' => 'page',
			'total_items' => $query->count(),
			'per_page' => \Config::get('news.viewParams.admin.list.limit'),
			'num_links' => 4,
			'show_first' => true,
			'show_last' => true,
		);
		$pagination = \Pagination::forge('mypagination', $config);
		$data['list'] = $query->order_by('created_at', 'desc')
			->rows_limit($pagination->per_page)
			->rows_offset($pagination->offset)
			->get();
		$data['pagination'] = $pagination->render();

		$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs(term(array('news.view', 'site.management')));
		$this->template->subtitle = \View::forge('news/_parts/list_subtitle');
		$this->template->content = \View::forge('news/list', $data);
	}

	/**
	 * The list action.
	 * 
	 * @access  public
	 * @return  void
	 */
	Public function action_list()
	{	
		$this->action_index();
	}

	/**
	 * News detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		if (!$news = \News\Model_News::check_authority($id)) throw new \HttpNotFoundException;

		$images = \News\Model_NewsImage::get4news_id($id);

		$title = array('name' => $news->title);
		$header_info = array();
		if (!$news->is_published)
		{
			$header_info = array('body' => sprintf('この%sはまだ%sされていません。', term('news.view'), term('form.publish')));
		}
		//$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs($title, array('admin/news' => term('news.view', 'admin.view')), null, null, $header_info);
		$this->template->subtitle = \View::forge('news/_parts/detail_subtitle', array('news' => $news));
		$this->template->content = \View::forge('news/detail', array('news' => $news, 'images' => $images));
	}

	/**
	 * News create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$news = \News\Model_News::forge();
		$val = self::get_validation_object($news);
		$files = array();
		$is_enabled_image = \Config::get('news.image.isEnabled');

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$file_tmps = array();
			$moved_files = array();
			$news_image_ids = array();
			try
			{
				if ($is_enabled_image) $file_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize();
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				$news->title        = $post['title'];
				$news->body         = $post['body'];
				$news->users_id     = $this->u->id;
				$news->token        = \Util_toolkit::create_hash();
				$news->is_published = $post['is_draft'] ? 0 : 1;
				if ($post['published_at_time'])
				{
					$news->published_at = $post['published_at_time'].':00';
				}
				elseif ($news->is_published)
				{
					$news->published_at = date('Y-m-d H:i:s');
				}
				\DB::start_transaction();
				$news->save();
				if ($is_enabled_image)
				{
					list($moved_files, $news_image_ids) = \Site_FileTmp::save_images($file_tmps, $news->id, 'news_id', 'news_image', 'News', null, true);
				}

				//// timeline 投稿
				//if ($note->is_published && is_enabled('timeline'))
				//{
				//	\Timeline\Site_Model::save_timeline($this->u->id, $post['public_flag'], 'note', $note->id);
				//}
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				$message = sprintf('%sを%sしました。', term('news.view'), $news->is_published ? term('form.publish') : term('form.draft'));
				\Session::set_flash('message', $message);
				\Response::redirect('admin/news/detail/'.$news->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$files = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id, true);

				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(term('news.view', 'form.create'), array('admin/news' => term('news.view', 'admin.view')));
		$this->template->post_header = \View::forge('news/_parts/form_header');
		$this->template->post_footer = \View::forge('news/_parts/form_footer');
		$this->template->content = \View::forge('news/_parts/form', array('val' => $val, 'files' => $files));
	}

	/**
	 * News edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		if (!$id || !$news = \News\Model_News::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}

		$val = self::get_validation_object($news, true);
		$news_images = array();
		$files = array();
		if ($is_enabled_image = \Config::get('news.image.isEnabled'))
		{
			$news_images = \News\Model_NewsImage::get4news_id($news->id);
			$files = \Site_Upload::get_file_objects($news_images, $news->id, true);
		}

		$file_tmps = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$moved_files = array();
			$news_image_ids = array();
			try
			{
				if ($is_enabled_image) $file_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize();
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				$news->title = $post['title'];
				$news->body  = $post['body'];

				$message = sprintf('%sを%sしました。', term('news.view'), term('form.edit'));
				if ($is_published = (!$news->is_published && empty($post['is_draft'])))
				{
					$news->is_published = 1;
					$message = sprintf('%sを%sしました。', term('news.view'), term('form.publish'));
				}

				if ($post['published_at_time'] && !\Util_Date::check_is_same_minute($post['published_at_time'], $news->published_at))
				{
					$news->published_at = $post['published_at_time'].':00';
				}
				elseif ($is_published)
				{
					$news->published_at = date('Y-m-d H:i:s');
				}

				\DB::start_transaction();
				$news->save();
				if ($is_enabled_image)
				{
					list($moved_files, $news_image_ids) = \Site_FileTmp::save_images($file_tmps, $news->id, 'news_id', 'news_image', 'News', null, true);
					//\News\Model_NewsImage::save_multiple($news->id, $news_image_ids);
					\Site_Upload::update_image_objs4file_objects($news_images, $files);
				}

				//// timeline 投稿
				//if (is_enabled('timeline'))
				//{
				//	if ($is_published)
				//	{
				//		\Timeline\Site_Model::save_timeline($this->u->id, $note->public_flag, 'note', $note->id);
				//	}
				//	elseif ($is_update_public_flag)
				//	{
				//		// timeline の public_flag の更新
				//		\Timeline\Model_Timeline::update_public_flag4foreign_table_and_foreign_id($note->public_flag, 'note', $note->id, \Config::get('timeline.types.note'));
				//	}
				//}
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				\Session::set_flash('message', $message);
				\Response::redirect('admin/news/detail/'.$news->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$file_tmps = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id, true);

				\Session::set_flash('error', $e->getMessage());
			}
		}
		$files += $file_tmps;

		$this->set_title_and_breadcrumbs(term('form.edit'), array('admin/news' => term('news.view', 'admin.view'), 'admin/news/'.$news->id => $news->title));
		$this->template->post_header = \View::forge('news/_parts/form_header');
		$this->template->post_footer = \View::forge('news/_parts/form_footer');
		$this->template->content = \View::forge('news/_parts/form', array(
			'val' => $val,
			'news' => $news,
			'is_edit' => true,
			'files' => $files,
		));
	}

	/**
	 * News delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		if (!$id || !$news = \News\Model_News::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}

		$redirect_uri = \Input::post('destination');
		if (!$redirect_uri || !\Util_string::check_uri_for_redilrect($redirect_uri))
		{
			$redirect_uri = 'admin/news';
		}

		try
		{
			\DB::start_transaction();
			$deleted_files = $news->delete_with_relations();
			\DB::commit_transaction();
			if (!empty($deleted_files)) \Site_Upload::remove_files($deleted_files);
			\Session::set_flash('message', term('news.view').'を削除しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect($redirect_uri);
	}

	/**
	 * News publish
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_publish($id = null)
	{
		$this->change_publish_status($id, 1);
	}

	/**
	 * News unpublish
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_unpublish($id = null)
	{
		$this->change_publish_status($id, 0);
	}

	private function change_publish_status($id, $target_status)
	{
		$target_status = \Util_string::cast_bool_int($target_status);

		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		if (!$id || !$news = \News\Model_News::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}

		$msg_status = $target_status ? term('form.publish') : term('form.unpublish').'に';
		$redirect_uri = \Input::post('destination');
		if (!$redirect_uri || !\Util_string::check_uri_for_redilrect($redirect_uri))
		{
			$redirect_uri = 'admin/news'.$id;
		}

		if ($news->is_published == $target_status)
		{
			\Session::set_flash('error', sprintf('既に%sされています。', $msg_status));
			\Response::redirect($redirect_uri);
		}

		try
		{
			\DB::start_transaction();
			$news->is_published = $target_status;
			if (!$news->published_at) $news->published_at = date('Y-m-d H:i:s');
			$news->save();

			//// timeline 投稿
			//if (is_enabled('timeline')) \Timeline\Site_Model::save_timeline($this->u->id, $note->public_flag, 'note', $note->id);
			\DB::commit_transaction();
			\Session::set_flash('message', sprintf('%sを%sしました。', term('news.view'), $msg_status));
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect($redirect_uri);
	}

	private static function get_validation_object(\News\Model_News $news, $is_edit = false)
	{
		$val = \Validation::forge();
		$val->add_model($news);

		$val->add('published_at_time', '公開日時')
				->add_rule('datetime_except_second');
		if (empty($news->is_published))
		{
			$val->add('is_draft', \Config::get('term.draft'))
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', array(0,1));
		}

		return $val;
	}
}

/* End of news.php */
