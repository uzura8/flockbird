<?php
namespace Album;

class AlreadySetToCoverException extends \FuelException {}

class Controller_Image_api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
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
	public function get_list($parent_id = null)
	{
		if ($this->format != 'html') throw new \HttpNotFoundException();

		$page = (int)\Input::get('page', 1);
		$response = '';
		try
		{
			if (!$album = Model_Album::check_authority($parent_id))
			{
				throw new \HttpNotFoundException;
			}
			$params = array(
				'where' => array('album_id', $parent_id),
				'related' => 'file',
				'limit' => \Config::get('album.article_list.limit'),
				'order_by' => array('created_at' => 'desc'),
			);
			$data = \Site_Model::get_simple_pager_list('album_image', $page, $params, 'Album');
			$data['album'] = $album;

			$response = \View::forge('image/_parts/list.php', $data);
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
	 * Api id_list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_id_list($parent_id = null)
	{
		$response = array();
		try
		{
			if (!$album = Model_Album::check_authority($parent_id))
			{
				throw new \HttpNotFoundException;
			}
			$response = Model_AlbumImage::find('all', array('where' => array('album_id' => $parent_id), 'related' => 'file', 'order_by_rows' => 'created_at'));
			$status_code = 200;
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
			$this->auth_check_api();
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
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
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
			$this->auth_check_api();
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$album_image = Model_AlbumImage::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}

			\DB::start_transaction();
			$deleted_filesize = Model_AlbumImage::delete_with_file($id);
			\Model_Member::add_filesize($this->u->id, -$deleted_filesize);
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
		}
		catch(\Exception $e)
		{
			\DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
