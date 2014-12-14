<?php
namespace Album;

class AlreadySetToCoverException extends \FuelException {}

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
	 * Api list
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_list($album_id = null)
	{
		$response  = '';
		try
		{
			$this->check_response_format(array('html', 'json'));

			$album_id  = (int)$album_id;
			if (!$album_id) $album_id = (int)\Input::get('album_id', 0);
			$member_id = (int)\Input::get('member_id', 0);
			$is_member_page = (int)\Input::get('is_member_page', 0);
			list($limit, $page) = $this->common_get_pager_list_params(\Config::get('album.articles.limit'), \Config::get('album.articles.limit_max'));

			$album     = null;
			$member    = null;
			$is_mypage = false;

			if ($album_id) $album = Model_Album::check_authority($album_id, null, 'member');
			if ($member_id) list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id, true);
			if ($album && $member)
			{
				$member = null;
				$is_mypage = false;
			}
			if (!$is_mypage && $album) $is_mypage = $this->check_is_mypage($album->member_id);

			$params = array(
				'related'  => array('album'),
				'order_by' => array('id' => 'desc'),
			);
			if ($limit) $params['limit'] = $limit;

			$target_member_id = 0;
			$self_member_id   = \Auth::check() ? $this->u->id : 0;
			$member_id_colmn  = null;
			$where            = array();
			if ($album) $where = array(array('album_id', $album_id));
			if ($member)
			{
				$target_member_id = $member->id;
				$member_id_colmn  = 't1.member_id';
			}
			$params['where'] = \Site_Model::get_where_params4list($target_member_id, $self_member_id, $is_mypage, $where, $member_id_colmn);
			$data = Model_AlbumImage::get_pager_list($params, $page);
			$data['liked_album_image_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
				\Site_Model::get_liked_ids('album_image', $this->u->id, $data['list'], 'Album') : array();

			if ($this->format == 'html')
			{
				$data['is_member_page'] = $is_member_page;
				if (!empty($album))  $data['album']  = $album;
				if (!empty($member)) $data['member'] = $member;

				$response = \View::forge('image/_parts/list', $data);
				$status_code = 200;

				return \Response::forge($response, $status_code);
			}

			$list_array = array();
			foreach ($data['list'] as $key => $obj)
			{
				$row = $obj->to_array();
				$row['album']['member'] = \Model_Member::get_basic_data($obj->album->member_id);
				$list_array[] = $row;
			}
			// json response
			$response = $list_array;
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Api member
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_member()
	{
		if ($this->format != 'html') throw new \HttpNotFoundException();

		$member_id = (int)\Input::get('member_id', 0);

		$response = '';
		try
		{
			list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id, true);
			list($limit, $page) = $this->common_get_pager_list_params(\Config::get('album.articles.limit'), \Config::get('album.articles.limit_max'));
			$data = Model_AlbumImage::get_pager_list(array(
				'related' => array('album'),
				'where' => array('t2.member_id', $member_id),
				'limit' => $limit,
				'order_by' => array('id' => 'desc'),
			), $page);
			$data['member'] = $member;
			$response = \View::forge('image/_parts/list', $data);
			$status_code = 200;

			return \Response::forge($response, $status_code);
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album_image get_menu
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_menu($id = null)
	{
		$response = '';
		try
		{
			$this->check_response_format('html');

			$is_detail  = (bool)\Input::get('is_detail', 0);
			$id = (int)$id;
			$album_image = Model_AlbumImage::check_authority($id);
			$member_id = $album_image->album->member_id;
			$this->check_browse_authority($album_image->public_flag, $member_id);

			$menus = array();
			if ($member_id == $this->u->id)
			{
				if (!$is_detail) $menus[] = array('tag' => 'divider');

				if ($album_image->album->foreign_table == 'member')
				{
					if ($album_image->file_name == $this->u->file_name)
					{
						$menus[] = array('tag' => 'disabled', 'icon_term' => term(array('profile', 'site.image', 'site.set_already')));
					}
					else
					{
						$menus[] = array('icon_term' => 'form.set_profile_image', 'href' => '#', 'attr' => array(
							'class' => 'js-simplePost',
							'data-uri' => 'member/profile/image/set/'.$album_image->id,
							'data-msg' => term(array('profile', 'site.image')).'に設定しますか？',
						));
					}
				}
				else
				{
					if ($album_image->album->cover_album_image_id == $album_image->id)
					{
						$menus[] = array('tag' => 'disabled', 'icon_term' => 'form.set_cover_already');
					}
					else
					{
						$menus[] = array('icon_term' => 'form.set_cover', 'attr' => array(
							'class' => 'js-update_toggle',
							'data-uri' => 'album/image/api/set_cover/'.$album_image->id.'.json',
						));
					}
				}
				$menus[] = array('href' => 'album/image/edit/'.$id, 'icon_term' => 'form.do_edit');
				$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
					'class' => $is_detail ? 'js-simplePost' : 'js-ajax-delete',
					'data-uri' => $is_detail ? 'album/image/delete/'.$album_image->id : 'album/image/api/delete/'.$id.'.json',
					'data-msg' => term('form.delete').'します。よろしいですか。',
					'data-parent' => 'main_item_'.$id,
				));
			}
			elseif (is_enabled('notice'))
			{
				$is_watched = \Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id('album_image', $id, $this->u->id);
				$menus[] = array('icon_term' => $is_watched ? 'form.do_unwatch' : 'form.do_watch', 'attr' => array(
					'class' => 'js-update_toggle',
					'data-uri' => 'member/notice/api/update_watch_status/album_image/'.$id,
					'data-msg' => $is_watched ? term('form.watch').'を解除しますか？' : term('form.watch').'しますか？',
				));
			}

			$response = \View::forge('_parts/dropdown_menu', array('menus' => $menus, 'is_ajax_loaded' => true));
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Api post_set_cover
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_set_cover($id = null)
	{
		$response = array('status' => 0, 'error_messages' => array());
		$response['error_messages']['default'] = term('form.watch').'状態の変更に失敗しました。';
		try
		{
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');
			$album_image = Model_AlbumImage::check_authority($id, $this->u->id);
			if ($album_image->album->cover_album_image_id == $id)
			{
				throw new AlreadySetToCoverException;
			}
			$album_image->album->cover_album_image_id = $id;
			\DB::start_transaction();
			$response['status'] = (bool)$album_image->album->save();
			\DB::commit_transaction();

			$response['album_id'] = $album_image->album_id;
			$response['html'] = html_tag('span', array('class' => 'disabled'), term('form.set_cover_already'));
			$response['is_replace'] = 1;
			$response['message'] = term('cover_image').'に設定しました。';
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(AlreadySetToCoverException $e)
		{
			$response['error_messages']['409'] = term('form.set_cover_already').'です。';
			$status_code = 409;
		}
		catch(\Database_Exception $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album image delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete($id = null)
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');

			\DB::start_transaction();
			$album_image = Model_AlbumImage::check_authority($id, $this->u->id);
			$album_image->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 401;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album image update public_flag
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function post_update_public_flag()
	{
		if ($this->format != 'html') throw new \HttpNotFoundException();
		$response = '0';
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			$icon_only_flag = (int)\Input::post('icon_only_flag', 0);
			$album_image = Model_AlbumImage::check_authority($id, $this->u->id);
			list($public_flag, $model) = \Site_Util::validate_params_for_update_public_flag($album_image->public_flag);

			\DB::start_transaction();
			$album_image->update_public_flag($public_flag);
			\DB::commit_transaction();

			$response = \View::forge('_parts/public_flag_selecter', array(
				'model' => $model,
				'id' => $id,
				'public_flag' => $public_flag,
				'is_mycontents' => true,
				'without_parent_box' => 'true',
				'view_icon_only' => $icon_only_flag,
			));
			$status_code = 200;

			return \Response::forge($response, $status_code);
		}
		catch(\DisableToUpdateException $e)
		{
			$response = json_encode(array('error' => array('message' => $e->getMessage())));
			$status_code = 403;
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(\FuelException $e)
		{
			\DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
