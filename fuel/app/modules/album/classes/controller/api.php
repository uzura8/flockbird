<?php
namespace Album;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
		'get_member',
		'get_slide',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get list
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
			list($limit, $page) = $this->common_get_pager_list_params(conf('album.articles.limit', 'album'), conf('album.articles.limit_max', 'album'));
			$data = Site_Model::get_albums($limit, $page, \Auth::check() ? $this->u->id : 0, $member, $is_mypage);
			$this->set_response_body_api($data, '_parts/list');
		});
	}

	/**
	 * Get list by member
	 * 
	 * @access  public
	 * @param   int  $member_id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_member($member_id = null)
	{
		$this->api_accept_formats = array('json', 'html');
		$this->controller_common_api(function() use($member_id)
		{
			list($is_mypage, $member) = $member_id ? $this->check_auth_and_is_mypage($member_id, true) : array(null, false);
			list($limit, $page) = $this->common_get_pager_list_params(conf('album.articles.limit', 'album'), conf('album.articles.limit_max', 'album'));

			$params = array();
			if ($select = (array)\Input::get('cols')) $params['select'] = $select;
			if ($limit = \Input::get('limit')) $params['limit'] = $limit;
			if (\Input::get('no_relateds')) $params['where'] = array(array('foreign_table', ''));
			$data = Site_Model::get_albums($limit, $page, \Auth::check() ? $this->u->id : 0, $member, $is_mypage, $params, $this->format == 'json');
			$this->set_response_body_api($data, $this->format == 'html' ? '_parts/list' : null);
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
		return $this->api_get_menu_common('album', $id, true, 'image_item_');
	}

	/**
	 * Delete album
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_delete($id = null)
	{
		return $this->api_delete_common('album', $id);
	}

	/**
	 * Update public_flag
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_update_public_flag($id = null)
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function() use($id)
		{
			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');
			$icon_only_flag = (int)\Input::post('icon_only_flag', 0);
			$have_children_public_flag      = (int)\Input::post('have_children_public_flag', 0);
			$is_update_children_public_flag = (int)\Input::post('is_update_children_public_flag', 0);
			$album = Model_Album::check_authority($id, $this->u->id, 'member');
			if ($result = Site_Util::check_album_disabled_to_update($album->foreign_table))
			{
				throw new \DisableToUpdateException($result['message']);
			}
			list($public_flag, $model) = \Site_Util::validate_params_for_update_public_flag($album->public_flag);

			\DB::start_transaction();
			$album->update_public_flag_with_relations($public_flag, !empty($is_update_children_public_flag));
			\DB::commit_transaction();

			$data = array(
				'model'              => $model,
				'id'                 => $id,
				'public_flag'        => $public_flag,
				'is_mycontents'      => true,
				'without_parent_box' => true,
				'view_icon_only'     => $icon_only_flag,
				'have_children_public_flag' => $have_children_public_flag,
				'child_model'        => 'album_image',
			);
			$this->set_response_body_api($data, '_parts/public_flag_selecter');
		});
	}
}
