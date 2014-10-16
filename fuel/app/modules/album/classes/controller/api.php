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
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');

			\DB::start_transaction();
			$album = Model_Album::check_authority($id, $this->u->id, 'member');
			$deleted_files = Model_Album::delete_relations($album);
			\DB::commit_transaction();
			if (!empty($deleted_files)) \Site_Upload::remove_files($deleted_files);

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
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
				throw new \DisableToUpdatePublicFlagException($result['message']);
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
		catch(\DisableToUpdatePublicFlagException $e)
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
