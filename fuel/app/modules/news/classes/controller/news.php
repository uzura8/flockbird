<?php
namespace News;

class Controller_News extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'category',
		'tag',
		'detail',
		'preview',
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
	 * News list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		list($limit, $page) = $this->common_get_pager_list_params();
		$data = Site_Model::get_list($limit, $page, \Auth::check());

		$this->set_title_and_breadcrumbs(t('news.plural'));
		$this->template->post_footer = \View::forge('_parts/list_footer');
		$this->template->content = \View::forge('_parts/list', $data);
		$this->template->content->set_safe('html_bodys', Site_Model::convert_raw_bodys($data['list']));
	}

	/**
	 * News category
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_category($category_name = null)
	{
		if (is_null($category_name) || !$news_category = Model_NewsCategory::get4name($category_name)) throw new \HttpNotFoundException;

		list($limit, $page) = $this->common_get_pager_list_params();
		$data = Site_Model::get_list($limit, $page, \Auth::check(), $news_category->id);
		$data['category_name'] = $category_name;

		$this->set_title_and_breadcrumbs($news_category->label, array('news/list' => t('news.plural')));
		$this->template->post_footer = \View::forge('_parts/list_footer');
		$this->template->content = \View::forge('_parts/list', $data);
		$this->template->content->set_safe('html_bodys', Site_Model::convert_raw_bodys($data['list']));
	}

	/**
	 * News tag
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_tag($tag_string = null)
	{
		$tags = \Site_Util::validate_tags($tag_string);
		if (!$tags || !$ids = \News\Model_NewsTag::get_news_ids4tags($tags)) throw new \HttpNotFoundException;

		list($limit, $page) = $this->common_get_pager_list_params();
		$data = Site_Model::get_list($limit, $page, \Auth::check(), null, $ids);
		$data['tag_string'] = implode(', ', $tags);

		$this->set_title_and_breadcrumbs(sprintf('%s: %s', term('site.tag'), implode(', ', $tags)), array('news/list' => t('news.plural')));
		$this->template->post_footer = \View::forge('_parts/list_footer');
		$this->template->content = \View::forge('_parts/list', $data);
		$this->template->content->set_safe('html_bodys', Site_Model::convert_raw_bodys($data['list']));
	}

	/**
	 * News detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($slug = null)
	{
		if (!$news = Model_News::get4slug($slug, true, 'news_category')) throw new \HttpNotFoundException;

		$images = \Config::get('news.image.isEnabled') ? \News\Model_NewsImage::get4news_id($news->id) : array();
		$files  = \Config::get('news.file.isEnabled') ? \News\Model_NewsFile::get4news_id($news->id) : array();
		$tags   = \Config::get('news.tags.isEnabled') ? \News\Model_NewsTag::get_names4news_id($news->id) : array();

		$ogp_infos = array(
			'title' => $news->title,
			'description' => strip_tags($news->body),
		);
		if ($images) $ogp_infos['image'] = \Site_Util::get_image_uri4image_list($images, 'nw', 'raw');
		$middle_breadcrumbs = array('news' => t('news.plural'));
		if ($news->news_category) $middle_breadcrumbs['news/category/'.$news->news_category->name] = $news->news_category->label;
		$this->set_title_and_breadcrumbs($news->title, $middle_breadcrumbs, null, null, null, false, false, $ogp_infos);

		$this->template->subtitle = \View::forge('_parts/news_subinfo', array('news' => $news));
		$this->template->content = \View::forge('detail', array('news' => $news, 'images' => $images, 'files' => $files, 'tags' => $tags));
		if (Site_Util::check_editor_enabled()) $this->template->content->set_safe('html_body', $news->body);
	}

	/**
	 * News preview
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_preview($slug = null)
	{
		if (!$news = Model_News::get4slug($slug, false)) throw new \HttpNotFoundException;
		$token = \Input::get('token');
		if (!$token || $token != $news->token) throw new \HttpNotFoundException;

		$images = \Config::get('news.image.isEnabled') ? \News\Model_NewsImage::get4news_id($news->id) : array();
		$files  = \Config::get('news.file.isEnabled') ? \News\Model_NewsFile::get4news_id($news->id) : array();
		$tags   = \Config::get('news.tags.isEnabled') ? \News\Model_NewsTag::get_names4news_id($news->id) : array();

		$title = array('name' => $news->title);
		$header_info = self::get_prview_header_info($news->is_published, $news->published_at);
		$this->set_title_and_breadcrumbs($title, null, null, null, $header_info, true);
		$this->template->subtitle = \View::forge('_parts/news_subinfo', array('news' => $news));
		$this->template->content = \View::forge('detail', array('news' => $news, 'images' => $images, 'files' => $files, 'tags' => $tags));
		if (Site_Util::check_editor_enabled()) $this->template->content->set_safe('html_body', $news->body);
	}

	private static function get_prview_header_info($is_published, $published_at)
	{
		$header_info = array();
		switch ($status = Site_Util::get_status($is_published, $published_at))
		{
			case 'closed':
				$header_info = array(
					'body' => __('message_not_published', array('label' => t('news.view'))),
					'type' => 'danger'
				);
				break;
			case 'reserved':
				$header_info = array(
					'body' => __('message_publish_reserved_at', array('label' => t('news.view'), 'time' => site_get_time($published_at, 'normal'))),
				);
				break;
		}

		return $header_info;
	}
}
