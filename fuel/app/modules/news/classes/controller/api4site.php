<?php
namespace News;

class Controller_Api4site extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get note list
	 * 
	 * @access  public
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list()
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function()
		{
			$category_name = \Input::get('category');
			$news_category = $category_name ? Model_NewsCategory::get4name($category_name) : null;

			$tag_string = \Input::get('tag');
			$tags = \Site_Util::validate_tags($tag_string);
			$ids = $tags ? Model_NewsTag::get_news_ids4tags($tags) : array();

			list($limit, $page) = $this->common_get_pager_list_params();
			$data = Site_Model::get_list($limit, $page, \Auth::check(), $news_category ? $news_category->id : 0, $ids);
			$data['category_name'] = $category_name;
			$data['tag_string'] = implode(', ', $tags);

			$this->set_response_body_api($data, '_parts/list', array('html_bodys' => Site_Model::convert_raw_bodys($data['list'])));
		});
	}
}

