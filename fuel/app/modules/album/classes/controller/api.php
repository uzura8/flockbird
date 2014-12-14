<?php
namespace Album;

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
	 * Api list
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_list()
	{
		$response = '';
		try
		{
			$this->check_response_format('html');

			$member_id = (int)\Input::get('member_id', 0);
			$is_member_page = (int)\Input::get('is_member_page', 0);
			list($limit, $page) = $this->common_get_pager_list_params(\Config::get('album.articles.limit'), \Config::get('album.articles.limit_max'));
			$data = Model_Album::get_pager_list(array(
				'related'  => 'member',
				'where'    => \Site_Model::get_where_params4list($member_id, \Auth::check() ? $this->u->id : 0, $this->check_is_mypage($member_id)),
				'limit'    => $limit,
				'order_by' => array('created_at' => 'desc'),
			), $page);
			$data['is_member_page'] = $is_member_page;

			$response = \View::forge('_parts/list', $data);
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
	 * Api get_menu
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
			$album = Model_Album::check_authority($id);
			$this->check_browse_authority($album->public_flag, $album->member_id);

			$menus = array();
			if ($album->member_id == $this->u->id)
			{
				if (!Site_Util::check_album_disabled_to_update($album->foreign_table, true))
				{
					if (!$is_detail) $menus[] = array('tag' => 'divider');
					$menus[] = array('href' => 'album/edit/'.$id, 'icon_term' => 'form.do_edit');
					$menus[] = array('icon_term' => 'form.do_delete', 'attr' => array(
						'class' => $is_detail ? 'js-simplePost' : 'js-ajax-delete',
						'data-uri' => $is_detail ? 'album/delete/'.$album->id : 'album/api/delete/'.$id.'.json',
						'data-msg' => term('form.delete').'します。よろしいですか。',
						'data-parent' => 'main_item_'.$id,
					));
				}
			}
			elseif (is_enabled('notice'))
			{
				$is_watched = \Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id('album_', $id, $this->u->id);
				$menus[] = array('icon_term' => $is_watched ? 'form.do_unwatch' : 'form.do_watch', 'attr' => array(
					'class' => 'js-update_toggle',
					'data-uri' => 'member/notice/api/update_watch_status/album/'.$id,
					//'data-msg' => $is_watched ? term('form.watch').'を解除しますか？' : term('form.watch').'しますか？',
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
			$status_code = 500;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Api albums
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_albums($cols = array('id', 'name'))
	{
		if ($this->format != 'json') throw new \HttpNotFoundException();

		$with_foreigns = (int)\Input::get('with_foreigns', 0);
		$albums = Model_Album::get4member_id($this->u->id, array('id', 'name'), $with_foreigns);

		$this->response($albums, 200);
	}

	/**
	 * Album delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete($id = null)
	{
		$response = array('status' => 0, 'error_messages' => array());
		$response['error_messages']['default'] = sprintf('%sの%sに失敗しました。', term('album'), term('form.delete'));
		try
		{
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');

			$album = Model_Album::check_authority($id, $this->u->id, 'member');
			if ($result = Site_Util::check_album_disabled_to_update($album->foreign_table))
			{
				throw new \DisableToUpdateException($result['message']);
			}
			\DB::start_transaction();
			$album->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
			$response['message'] = sprintf('%sを%sしました。', term('album'), term('form.delete'));
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
		catch(\DisableToUpdateException $e)
		{
			$response['error_messages']['absolute'] = $e->getMessage();
			$status_code = 403;
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(\Database_Exception $e)
		{
			$status_code = 500;
		}
		catch(\FuelException $e)
		{
			$status_code = 500;
		}
		if ($status_code == 500)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$response['error_messages']['500'] = $response['error_messages']['default'];
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album update public_flag
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

			$id             = (int)\Input::post('id');
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

			$response = \View::forge('_parts/public_flag_selecter', array(
				'model'              => $model,
				'id'                 => $id,
				'public_flag'        => $public_flag,
				'is_mycontents'      => true,
				'without_parent_box' => true,
				'view_icon_only'     => $icon_only_flag,
				'have_children_public_flag' => $have_children_public_flag,
				'child_model'        => 'album_image',
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
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
