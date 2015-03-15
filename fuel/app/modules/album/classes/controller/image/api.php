<?php
namespace Album;

class Controller_Image_api extends \Controller_Site_Api
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
	 * Get list
	 * 
	 * @access  public
	 * @param   int     $parent_id  target parent record id
	 * @return  Response(json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list($parent_id = null)
	{
		$this->api_accept_formats = array('html', 'json');
		$this->controller_common_api(function() use($parent_id)
		{
			$album_id       = (int)\Input::get('album_id') ?: (int)$parent_id;
			$member_id      = (int)\Input::get('member_id', 0);
			$is_member_page = (int)\Input::get('is_member_page', 0);
			$album          = $album_id ? Model_Album::check_authority($album_id, null, 'member') : null;
			list($is_mypage, $member) = $member_id ? $this->check_auth_and_is_mypage($member_id, true) : array(null, false);
			if ($album && $member)
			{
				$member = null;
				$is_mypage = false;
			}
			if (!$is_mypage && $album) $is_mypage = $this->check_is_mypage($album->member_id);
			list($limit, $page) = $this->common_get_pager_list_params(conf('album.articles.limit', 'album'), conf('album.articles.limit_max', 'album'));
			$params = array();
			if ($album) $params['where'] = array('album_id', $album_id);
			$data = Site_Model::get_album_images($limit, $page, get_uid(), $member, $is_mypage, $params, $this->format != 'html');
			$data['liked_album_image_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
				\Site_Model::get_liked_ids('album_image', $this->u->id, $data['list']) : array();

			if ($this->format == 'html')
			{
				$data['is_member_page'] = $is_member_page;
				if (!empty($album))  $data['album']  = $album;
				if (!empty($member)) $data['member'] = $member;
			}
			else
			{
				$list_array = array();
				foreach ($data['list'] as $key => $obj)
				{
					$row = $obj->to_array();
					$row['album']['member'] = \Model_Member::get_one_basic4id($obj->album->member_id);
					$list_array[] = $row;
				}
				// json response
				$data = $list_array;
			}

			$this->set_response_body_api($data, $this->format == 'html' ? 'image/_parts/list' : null);
		});
	}

	/**
	 * Get list by member
	 * 
	 * @access  public
	 * @param   int  $member_id  target member_id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_member($member_id = null)
	{
		$this->api_accept_formats = array('html');
		$this->controller_common_api(function() use($member_id)
		{
			$member_id = \Input::get('member_id', 0) ?: $member_id;
			list($is_mypage, $member) = $member_id ? $this->check_auth_and_is_mypage($member_id, true) : array(null, false);
			list($limit, $page) = $this->common_get_pager_list_params(conf('album.articles.limit', 'album'), conf('album.articles.limit_max', 'album'));
			$data = Site_Model::get_album_images($limit, $page, get_uid(), $member, $is_mypage, null, $this->format != 'html');
			$data['liked_album_image_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
				\Site_Model::get_liked_ids('album_image', $this->u->id, $data['list']) : array();

			if ($this->format == 'html')
			{
				if ($member) $data['member'] = $member;
			}
			else
			{
				$list_array = array();
				foreach ($data['list'] as $key => $obj)
				{
					$row = $obj->to_array();
					$row['album']['member'] = \Model_Member::get_one_basic4id($obj->album->member_id);
					$list_array[] = $row;
				}
				// json response
				$data = $list_array;
			}

			$this->set_response_body_api($data, $this->format == 'html' ? 'image/_parts/list' : null);
		});
	}

	/**
	 * Get note edit menu
	 * 
	 * @access  public
	 * @param   int  $id  album_image_id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_get_menu_common
	 */
	public function get_menu($id = null)
	{
		return $this->api_get_menu_common('album_image', $id, true, 'image_item_', 'album');
	}

	/**
	 * Set album cover image
	 * 
	 * @access  public
	 * @param   int  $id  album_image_id
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_set_cover($id = null)
	{
		$this->controller_common_api(function() use($id)
		{
			$this->response_body['errors']['message_default'] = term('form.watch').'状態の変更に失敗しました。';
			$id = intval(\Input::post('id') ?: $id);
			$album_image = Model_AlbumImage::check_authority($id, $this->u->id);
			if ($album_image->album->cover_album_image_id == $id)
			{
				throw new DisableToUpdateException(term('form.set_cover_already').'です。');
			}
			$album_image->album->cover_album_image_id = $id;
			\DB::start_transaction();
			$status = (bool)$album_image->album->save();
			\DB::commit_transaction();
			$data = array(
				'status' => $status,
				'album_id' => $album_image->album_id,
				'html' => html_tag('span', array('class' => 'disabled'), term('form.set_cover_already')),
				'is_replace' => 1,
				'message' => term('cover_image').'に設定しました。',
			);

			$this->set_response_body_api($data);
		});
	}


	/**
	 * Save location data.
	 * 
	 * @access  public
	 * @param   int  $id  album_image_id
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_save_location($id = null)
	{
		$this->controller_common_api(function() use($id)
		{
			$this->response_body['errors']['message_default'] = sprintf('%sの%sに失敗しました。', term('site.location'), term('form.save'));
			$id = intval(\Input::post('id') ?: $id);
			$album_image = Model_AlbumImage::check_authority($id, $this->u->id, 'album_image_location');

			$album_image_location = $album_image->album_image_location ?: Model_AlbumImageLocation::forge();
			$val = \Validation::forge();
			$val->add_model($album_image_location);
			$val->fieldset()->field('album_image_id')->delete_rule('required');
			if (!$val->run()) throw new \ValidationFailedException($val->show_errors());
			$post = $val->validated();

			$album_image_location->album_image_id = $id;
			$album_image_location->latitude = $post['latitude'];
			$album_image_location->longitude = $post['longitude'];
			\DB::start_transaction();
			$status = (bool)$album_image_location->save();
			\DB::commit_transaction();
			$data = array(
				'status' => $status,
				'album_image_location' => $album_image_location->to_array(),
				'message' => sprintf('%sを%sしました。', term('site.location'), term('form.save')),
			);

			$this->set_response_body_api($data);
		});
	}

	/**
	 * Delete album_image
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_api_delete_common
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('album_image', $id);
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
		$this->api_update_public_flag_common('album_image', $id);
	}
}
