<?php
namespace Note;

class Controller_Like_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_member',
	);

	/**
	 * Update like status
	 * 
	 * @access  public
	 * @param   int  $parent_id  target parent id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_update_like_common
	 */
	public function post_update($parent_id = null)
	{
		return $this->api_update_like_common('note', $parent_id);
	}

	/**
	 * Get liked members
	 * 
	 * @access  public
	 * @param   int  $parent_id  target parent id
	 * @return  Response (json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_get_liked_members_common
	 */
	public function get_member($parent_id = null)
	{
		return $this->api_get_liked_members_common('note', $parent_id);
	}
}
