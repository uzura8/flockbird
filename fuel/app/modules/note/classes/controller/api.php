<?php
namespace Note;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
		'get_member',
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
			$member_id = (int)\Input::get('member_id', 0);
			list($is_mypage, $member) = $member_id ? $this->check_auth_and_is_mypage($member_id, true) : array(null, false);
			list($limit, $page) = $this->common_get_pager_list_params();
			$is_draft = $is_mypage ? \Util_string::cast_bool_int(\Input::get('is_draft', 0)) : 0;
			$data = Site_Model::get_list($limit, $page, \Auth::check() ? $this->u->id : 0, $member, $is_mypage, $is_draft);
			$this->set_response_body_api($data, '_parts/list');
		});
	}

	/**
	 * Get note list by member
	 * 
	 * @access  public
	 * @param   int  $member_id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_member($member_id = null)
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function() use($member_id)
		{
			$member_id = (int)$member_id;
			list($is_mypage, $member) = $member_id ? $this->check_auth_and_is_mypage($member_id, true) : array(null, false);
			list($limit, $page) = $this->common_get_pager_list_params();
			$is_draft = $is_mypage ? \Util_string::cast_bool_int(\Input::get('is_draft', 0)) : 0;
			$data = Site_Model::get_list($limit, $page, \Auth::check() ? $this->u->id : 0, $member, $is_mypage, $is_draft);
			$this->set_response_body_api($data, '_parts/list');
		});
	}

	/**
	 * Get note edit menu
	 * 
	 * @access  public
	 * @param   int  $id  note id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_get_menu_common
	 */
	public function get_menu($id = null)
	{
		$this->api_get_menu_common('note', $id, true);
	}

	/**
	 * Delete note
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('note', $id, 'delete_with_relations');
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
		$this->api_update_public_flag_common('note', $id, 'update_public_flag_with_relations');
	}
}

