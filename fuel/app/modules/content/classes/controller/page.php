<?php
namespace Content;

class Controller_Page extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'detail',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Action index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index($slug = null)
	{
		$this->action_detail($slug);
	}

	/**
	 * Action detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($slug = null)
	{
		$content_page = Model_ContentPage::check_authority4unique_key('slug', $slug);
		$this->check_browse_authority(\Site_Util::convert_is_secure2public_flag($content_page->is_secure));

		$this->set_title_and_breadcrumbs($content_page->title);
		$this->template->content = \View::forge('page/detail', array('content_page' => $content_page));
		if (\Config::get('content.page.form.isEnabledWysiwygEditor')) $this->template->content->set_safe('html_body', $content_page->body);
	}
}
