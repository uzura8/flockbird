<?php
namespace Thread;

class Controller_Comment_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get thread comments
	 * 
	 * @access  public
	 * @param   int  $parent_id  target parent id
	 * @return  Response (json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::get_comment_list
	 */
	public function get_list($parent_id = null)
	{
		$this->api_get_comments_common('thread', $parent_id);
	}


	/**
	 * Create thread comment
	 * 
	 * @access  public
	 * @param   int     $parent_id  target parent id
	 * @return  Response(json)
	 * @see  Controller_Site_Api::api_create_comment_common
	 */
	public function post_create($parent_id = null)
	{
		$this->api_create_comment_common('thread', $parent_id);
	}


	/**
	 * Delete thread comment
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_api_delete_common
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('thread_comment', $id);
	}
}
