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
			'per_page' => 2,
			'num_links' => 4,
			'show_first' => true,
			'show_last' => true,
		);
		$pagination = \Pagination::forge('mypagination', $config);
		$data['list'] = $query->order_by('updated_at', 'desc')
			->rows_limit($pagination->per_page)
			->rows_offset($pagination->offset)
			->get();
		//$data['pagination'] = $pagination;
		$data['pagination'] = $pagination->render();

		//$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs(term(array('news.view', 'site.management')));
		$this->template->content = \View::forge('news/list', $data);
		//$this->template->post_footer = \View::forge('_parts/load_item');
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
			$title['label'] = array('name' => term('form.draft'));
			$header_info = array('body' => sprintf('この%sはまだ%sされていません。', term('news.view'), term('form.publish')));
		}
		//$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs($title, null, null, null, $header_info);
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
					list($moved_files, $news_image_ids) = \Site_FileTmp::save_images($file_tmps, $news->id, 'news_id', 'news_image', 'News');
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

		$this->set_title_and_breadcrumbs(term(array('news.view', 'form.create')));
		$this->template->post_header = \View::forge('news/_parts/form_header');
		$this->template->post_footer = \View::forge('news/_parts/form_footer');
		$this->template->content = \View::forge('news/_parts/form', array('val' => $val, 'files' => $files));
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
