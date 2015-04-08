<?php
namespace Admin;

class Controller_News extends Controller_Admin
{
	protected $check_not_auth_action = array();

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
		$data['list'] = $query->related('news_category')
			->order_by('created_at', 'desc')
			->rows_limit($pagination->per_page)
			->rows_offset($pagination->offset)
			->get();
		$data['pagination'] = $pagination->render();

		$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs(term('news.view', 'site.management'));
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
		$news = \News\Model_News::check_authority($id);

		$images = \Config::get('news.image.isEnabled') ? \News\Model_NewsImage::get4news_id($id) : array();
		$files  = \Config::get('news.file.isEnabled') ? \News\Model_NewsFile::get4news_id($id) : array();

		$title = array('name' => $news->title);
		$header_info = array();
		if (!$news->is_published)
		{
			$header_info = array('body' => sprintf('この%sはまだ%sされていません。', term('news.view'), term('form.publish')));
		}
		//$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs($title, array('admin/news' => term('news.view', 'admin.view')), null, null, $header_info);
		$this->template->subtitle = \View::forge('news/_parts/detail_subtitle', array('news' => $news));
		$this->template->content = \View::forge('news/detail', array('news' => $news, 'images' => $images, 'files' => $files));
		if (\News\Site_Util::check_editor_enabled()) $this->template->content->set_safe('html_body', $news->body);
	}

	/**
	 * News create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		// if insert image mode, forbidden to display create form.
		if (conf('image.isInsertBody', 'news'))
		{
			throw new \HttpNotFoundException;
		}

		$news = \News\Model_News::forge();
		$val = self::get_validation_object($news);
		$images = array();
		$files  = array();
		$is_enabled_image = \Config::get('news.image.isEnabled');
		$is_enabled_file  = \Config::get('news.file.isEnabled');
		$is_enabled_link  = \Config::get('news.link.isEnabled');
		$posted_links = array();

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			if ($is_enabled_link)
			{
				$posted_links = $this->get_posted_links();
				$val = $this->add_validation_object_posted_links($val, $posted_links);
			}
			$image_tmps = array();
			$file_tmps = array();
			$moved_images = array();
			$moved_files = array();
			$news_image_ids = array();
			$news_file_ids = array();
			$error_message = '';
			try
			{
				if ($is_enabled_image) $image_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize();
				if ($is_enabled_file)  $file_tmps  = \Site_FileTmp::get_file_tmps_and_check_filesize(null, null, 'file');
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				$news->set_values($post);
				$news->users_id     = $this->u->id;
				$news->token        = \Security::generate_token();
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
					list($moved_images, $news_image_ids) = \Site_FileTmp::save_images($image_tmps, $news->id, 'news_id', 'news_image', null, true);
				}
				if ($is_enabled_file)
				{
					list($moved_files, $news_file_ids) = \Site_FileTmp::save_images($file_tmps, $news->id, 'news_id', 'news_file', null, true, 'file');
				}
				if ($is_enabled_link)
				{
					$this->save_posted_links($posted_links, $news->id);
				}

				//// timeline 投稿
				//if ($note->is_published && is_enabled('timeline'))
				//{
				//	\Timeline\Site_Model::save_timeline($this->u->id, $post['public_flag'], 'note', $note->id);
				//}
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_images);

				$message = sprintf('%sを%sしました。', term('news.view'), $news->is_published ? term('form.publish') : term('form.draft'));
				\Session::set_flash('message', $message);
				\Response::redirect('admin/news/detail/'.$news->id);
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_images) \Site_FileTmp::move_files_to_tmp_dir($moved_images);
				if ($moved_files)  \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$images = \Site_FileTmp::get_file_objects($image_tmps, $this->u->id, true, 'img');
				$files  = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id, true, 'file');
				\Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(term('news.view', 'form.create'), array('admin/news' => term('news.view', 'admin.view')));
		$this->template->post_header = \View::forge('news/_parts/form_header');
		$this->template->post_footer = \View::forge('news/_parts/form_footer');
		$this->template->content = \View::forge('news/_parts/form', array('val' => $val, 'images' => $images, 'files' => $files, 'posted_links' => $posted_links));
	}

	/**
	 * News create_instantly
	 *   if insert image mode, forbidden to display create form.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create_instantly()
	{
		if (!conf('image.isInsertBody', 'news')) throw new \HttpNotFoundException;
		\Util_security::check_method('post');
		\Util_security::check_csrf();
		$news = \News\Model_News::create_instantly($this->u->id);
		\Response::redirect('admin/news/edit/'.$news->id);
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
		$news = \News\Model_News::check_authority($id);
		$val = self::get_validation_object($news);
		$news_images = array();
		$news_files = array();
		$images = array();
		$files  = array();
		$is_enabled_image = conf('image.isEnabled', 'news');
		$is_insert_body_image = conf('image.isInsertBody', 'news');
		$is_modal_upload_image = conf('image.isModalUpload', 'news');
		if ($is_enabled_image && !$is_modal_upload_image)
		{
			$news_images = \News\Model_NewsImage::get4news_id($news->id);
			$images = \Site_Upload::get_file_objects($news_images, $news->id, true, null, 'img', $is_insert_body_image);
		}
		if ($is_enabled_file = \Config::get('news.file.isEnabled'))
		{
			$news_files = \News\Model_NewsFile::get4news_id($news->id);
			$files = \Site_Upload::get_file_objects($news_files, $news->id, true, null, 'file');
		}

		$posted_links = array();
		$saved_links = array();
		if ($is_enabled_link  = \Config::get('news.link.isEnabled'))
		{
			$saved_links = $this->get_saved_links($news->id);
		}

		$image_tmps = array();
		$file_tmps = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			if ($is_enabled_link)
			{
				$posted_links = $this->get_posted_links();
				$val = $this->add_validation_object_posted_links($val, $saved_links, true);
				$val = $this->add_validation_object_posted_links($val, $posted_links);
			}

			$moved_images = array();
			$moved_files = array();
			$news_image_ids = array();
			$news_file_ids = array();
			$error_message = '';
			try
			{
				if ($is_enabled_image) $image_tmps = \Site_FileTmp::get_file_tmps_and_check_filesize();
				if ($is_enabled_file)  $file_tmps  = \Site_FileTmp::get_file_tmps_and_check_filesize(null, null, 'file');

				// 識別名の変更がない場合は unique を確認しない
				if (trim(\Input::post('slug')) == $news->slug) $val->fieldset()->field('slug')->delete_rule('unique');

				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				$news->set_values($post);

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
					list($moved_images, $news_image_ids) = \Site_FileTmp::save_images($image_tmps, $news->id, 'news_id', 'news_image', null, true);
					\Site_Upload::update_image_objs4file_objects($news_images, $images);
				}
				if ($is_enabled_file)
				{
					list($moved_files, $news_file_ids) = \Site_FileTmp::save_images($file_tmps, $news->id, 'news_id', 'news_file', null, true, 'file');
					\Site_Upload::update_image_objs4file_objects($news_files, $files);
				}
				if ($is_enabled_link)
				{
					$this->save_posted_links($saved_links, $news->id, true);
					$this->save_posted_links($posted_links, $news->id);
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
				\Site_FileTmp::make_and_remove_thumbnails($moved_images);

				\Session::set_flash('message', $message);
				\Response::redirect('admin/news/detail/'.$news->id);
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_images) \Site_FileTmp::move_files_to_tmp_dir($moved_images);
				if ($moved_files)  \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$image_tmps = \Site_FileTmp::get_file_objects($image_tmps, $this->u->id, true, 'img');
				$file_tmps  = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id, true, 'file');
				\Session::set_flash('error', $error_message);
			}
		}
		$images = array_merge($images, $image_tmps);
		$files = array_merge($files, $file_tmps);

		$this->set_title_and_breadcrumbs(term('form.edit'), array('admin/news' => term('news.view', 'admin.view'), 'admin/news/'.$news->id => $news->title));
		$this->template->post_header = \View::forge('news/_parts/form_header');
		$this->template->post_footer = \View::forge('news/_parts/form_footer');
		$this->template->content = \View::forge('news/_parts/form', array(
			'val' => $val,
			'saved_links' => $saved_links,
			'posted_links' => $posted_links,
			'news' => $news,
			'is_edit' => true,
			'images' => $images,
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
		try
		{
			\DB::start_transaction();
			$news = \News\Model_News::check_authority($id);
			$news->delete();
			\DB::commit_transaction();
			\Session::set_flash('message', term('news.view').'を削除しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect(\Site_Util::get_redirect_uri('admin/news'));
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
		$news = \News\Model_News::check_authority($id);

		$msg_status = $target_status ? term('form.publish') : term('form.unpublish').'に';
		$redirect_uri = \Site_Util::get_redirect_uri('admin/news/'.$id);

		if ($news->is_published == $target_status)
		{
			\Session::set_flash('error', sprintf('既に%sされています。', $msg_status));
			\Response::redirect($redirect_uri);
		}

		try
		{
			\DB::start_transaction();
			$news->is_published = $target_status;
			if ($news->is_published && !isset_datatime($news->published_at))
			{
				$news->published_at = date('Y-m-d H:i:s');
			}
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

	private function get_posted_links()
	{
		$posted_link_uris   = \Input::post('link_uri', array());
		$posted_link_labels = \Input::post('link_label', array());
		$posted_links = array();
		foreach ($posted_link_uris as $id => $uri)
		{
			$id = (int)$id;
			$posted_links[$id] = array('uri' => $uri);
			if (!empty($posted_link_labels[$id])) $posted_links[$id]['label'] = $posted_link_labels[$id];
		}

		return $posted_links;
	}

	private function get_saved_links($news_id)
	{
		$posted_link_uris   = \Input::post('link_uri_saved', array());
		$posted_link_labels = \Input::post('link_label_saved', array());

		$news_links = \News\Model_NewsLink::get4news_id($news_id);
		$saved_links = array();
		foreach ($news_links as $id => $news_link)
		{
			$id = (int)$id;
			$saved_links[$id] = array();
			$saved_links[$id]['uri']   = isset($posted_link_uris[$id]) ? $posted_link_uris[$id] : $news_link->uri;
			$saved_links[$id]['label'] = isset($posted_link_labels[$id]) ? $posted_link_labels[$id] : $news_link->label;
		}

		return $saved_links;
	}

	private static function get_validation_object(\News\Model_News $news)
	{
		$val = \Validation::forge();
		$val->add_model($news);

		$val->add('published_at_time', '公開日時')
				->add_rule('datetime_except_second');
		if (empty($news->is_published))
		{
			$val->add('is_draft', term('form.draft'))
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', array(0,1));
		}

		return $val;
	}

	private function add_validation_object_posted_links(\Validation $val, $links, $is_saved = false)
	{
		$posted_link_uris   = \Input::post('link_uri_saved', array());
		$posted_link_labels = \Input::post('link_label_saved', array());

		foreach ($links as $id => $values)
		{
			if ($is_saved && !isset($posted_link_uris[$id]) && !isset($posted_link_labels[$id])) continue;
			if (empty($values['uri']) && empty($values['label'])) continue;

			$val->add(sprintf('link_uri%s[%d]', $is_saved ? '_saved' : '', $id), 'リンクURL')
					->add_rule('trim')
					->add_rule('required')
					->add_rule('valid_url');
		}

		return $val;
	}

	private function save_posted_links($links, $news_id, $is_saved = false)
	{
		$posted_link_uris   = \Input::post('link_uri_saved', array());
		$posted_link_labels = \Input::post('link_label_saved', array());

		$saved_ids = array();
		foreach ($links as $id => $values)
		{
			$news_link = $is_saved ? \News\Model_NewsLink::check_authority($id) : \News\Model_NewsLink::forge();
			if ($is_saved && !isset($posted_link_uris[$id]) && !isset($posted_link_labels[$id]))
			{
				$news_link->delete();
				continue;
			}

			if (!$is_saved) $news_link->news_id = (int)$news_id;
			$is_upated = false;

			$uri = trim($values['uri']);
			if ($news_link->uri != $uri)
			{
				$news_link->uri = $uri;
				$is_upated = true;
			}

			$label = isset($values['label']) ? trim($values['label']) : '';
			if ($news_link->label != $label)
			{
				$news_link->label = $label;
				$is_upated = true;
			}

			if ($is_upated && !$news_link->save()) throw new \FuelException('Link url save error.');;
			$saved_ids[] = $news_link->id;
		}

		return $saved_ids;
	}
}

/* End of news.php */
