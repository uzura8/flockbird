<?php
namespace Thread;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list'
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get thread list
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
			list($limit, $page) = $this->common_get_pager_list_params();
			$data = Site_Model::get_list($limit, $page, get_uid());
			$this->set_response_body_api($data, '_parts/list');
		});
	}

	/**
	 * Get thread edit menu
	 * 
	 * @access  public
	 * @param   int  $id  thread id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_get_menu_common
	 */
	public function get_menu($id = null)
	{
		$this->api_get_menu_common('thread', $id, true);
	}

	/**
	 * Delete thread
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_delete($id = null)
	{
		return $this->api_delete_common('thread', $id);
	}

	/**
	 * Update public_flag
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_update_public_flag_common
	 */
	public function post_update_public_flag($id = null)
	{
		$this->api_update_public_flag_common('thread', $id, 'update_public_flag', 'public');
	}
}
