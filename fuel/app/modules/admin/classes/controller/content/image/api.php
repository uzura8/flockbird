<?php
namespace Admin;

class Controller_Content_Image_Api extends Controller_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}


	/**
	 * Get image list
	 * 
	 * @access  public
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list()
	{
		$this->api_accept_formats = array('html', 'json');
		$this->controller_common_api(function()
		{
			list($limit, $page) = $this->common_get_pager_list_params(\Config::get('admin.articles.images.limit'), \Config::get('admin.articles.images.limit_max'));
			$params = array('order_by' => array('id' => 'desc'));
			if ($limit) $params['limit'] = $limit;
			$data = \Model_SiteImage::get_pager_list($params, $page, $this->format == 'json');
			$this->set_response_body_api($data, $this->format == 'html' ? 'content/image/_parts/list' : null);
		});
	}

	/**
	 * Delete image
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('site_image', $id, null, term('site.image'));
	}
}
