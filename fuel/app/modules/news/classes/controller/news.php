<?php
namespace News;

class Controller_News extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'preview',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * News detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_preview($slug = null)
	{
		if (!$news = Model_News::get4slug($slug)) throw new \HttpNotFoundException;
		$token = \Input::get('token');
		if (!$token || $token != $news->token) throw new \HttpNotFoundException;

		$images = \Config::get('news.image.isEnabled') ? \News\Model_NewsImage::get4news_id($news->id) : array();
		$files  = \Config::get('news.file.isEnabled') ? \News\Model_NewsFile::get4news_id($news->id) : array();
		$tags   = \Config::get('news.form.tags.isEnabled') ? \News\Model_NewsTag::get_names4news_id($news->id) : array();

		$title = array('name' => $news->title);
		$header_info = self::get_prview_header_info($news->is_published, $news->published_at);
		$this->set_title_and_breadcrumbs($title, null, null, null, $header_info, true);
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
					'body' => sprintf('この%sはまだ%sされていません。', term('news.view'), term('form.publish')),
					'type' => 'danger'
				);
				break;
			case 'reserved':
				$header_info = array(
					'body' => sprintf('この%sは %s に%sされます。', term('news.view'), site_get_time($published_at, 'normal', 'Y/m/d H:i'), term('form.publish')),
				);
				break;
		}

		return $header_info;
	}
}
