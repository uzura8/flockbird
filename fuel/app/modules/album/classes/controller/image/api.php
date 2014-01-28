<?php
namespace Album;

class AlreadySetToCoverException extends \FuelException {}

class Controller_Image_api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
		'get_member',
		'get_comments',
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
		if (!in_array($this->format, array('html', 'json'))) throw new \HttpNotFoundException();

		$page      = (int)\Input::get('page', 1);
		$album_id  = (int)\Input::get('album_id', 0);
		$member_id = (int)\Input::get('member_id', 0);
		$is_member_page = (int)\Input::get('is_member_page', 0);
		$limit = (int)\Input::get('limit', \Config::get('album.articles.limit'));

		$album     = null;
		$member    = null;
		$is_mypage = false;

		$response  = '';
		try
		{
			if ($album_id && !$album = Model_Album::check_authority($album_id))
			{
				throw new \HttpNotFoundException;
			}
			if ($member_id) list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id, true);
			if ($album && $member)
			{
				$member = null;
				$is_mypage = false;
			}
			if (!$is_mypage && $album) $is_mypage = $this->check_is_mypage($album->member_id);

			$data = array('album' => null, 'member' => null);
			$params = array(
				'related'  => array('file', 'album'),
				'order_by' => array('shot_at' => 'asc'),
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
				$member_id_colmn  = 't2.member_id';
			}
			$params['where'] = \Site_Model::get_where_params4list($target_member_id, $self_member_id, $is_mypage, $where, $member_id_colmn);
			$data = \Site_Model::get_simple_pager_list('album_image', $page, $params, 'Album');

			if ($this->format == 'html')
			{
				$data['is_member_page'] = $is_member_page;
				if (!empty($album))  $data['album']  = $album;
				if (!empty($member)) $data['member'] = $member;

				$response = \View::forge('image/_parts/list', $data);
				$status_code = 200;

				return \Response::forge($response, $status_code);
			}

			$response = $data['list'];
			$status_code = 200;
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

		$page      = (int)\Input::get('page', 1);
		$member_id = (int)\Input::get('member_id', 0);

		$response = '';
		try
		{
			list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id, true);
			$data = \Site_Model::get_simple_pager_list('album_image', $page, array(
				'related' => array('file', 'album'),
				'where' => array('t2.member_id', $member_id),
				'limit' => \Config::get('album.articles.limit'),
				'order_by' => array('created_at' => 'desc'),
			), 'Album');
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
	 * Api comments
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_comments($id = null)
	{
		$id = (int)$id;
		if (!$id || !$album_image = Model_AlbumImage::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}
		$comments = Model_AlbumImageComment::find('all', array('where' => array('album_image_id' => $id), 'related' => 'member', 'order_by_rows' => 'id'));

		$this->response($comments);
	}

	/**
	 * Api post_set_cover
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_set_cover()
	{
		$response = array('status' => 0, 'message' => '');
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');

			if (!$id || !$album_image = Model_AlbumImage::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			if ($album_image->album->cover_album_image_id == $id)
			{
				throw new AlreadySetToCoverException;
			}
			$album_image->album->cover_album_image_id = $id;
			$album_image->album->save();
			if (!$album_image->album_id) throw new \HttpServerErrorException;

			$response['status'] = 1;
			$response['album_id'] = $album_image->album_id;
			$status_code = 200;
		}
		catch(AlreadySetToCoverException $e)
		{
			$response['message'] = 'カバー写真に既に登録済みです。';
			$status_code = 409;
		}
		catch(\HttpServerErrorException $e)
		{
			$status_code = 500;
		}
		catch(\Exception $e)
		{
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
	public function post_delete()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$album_image = Model_AlbumImage::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}

			\DB::start_transaction();
			$album_image->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
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
			if (!$id || !$album_image = Model_AlbumImage::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
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
			\DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
