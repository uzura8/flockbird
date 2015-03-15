<?php
namespace Album;

class Controller_Image_Comment_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get album_image comments
	 * 
	 * @access  public
	 * @param   int  $parent_id  target parent id
	 * @return  Response (json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::get_comment_list
	 */
	public function get_list($parent_id = null)
	{
		$this->api_get_comments_common('album_image', $parent_id, 'album', array('album' => 'member_id'));
	}

	/**
	 * Create album_image comment
	 * 
	 * @access  public
	 * @param   int     $parent_id  target parent id
	 * @return  Response(json)
	 * @see  Controller_Site_Api::api_create_comment_common
	 */
	public function post_create($parent_id = null)
	{
		$this->api_create_comment_common('album_image', $parent_id, 'album');
	}

	/**
	 * Delete album_image comment
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_api_delete_common
	 */
	public function post_delete($id = null)
	{
		return $this->api_delete_common('album_image_comment', $id);
	}
}
