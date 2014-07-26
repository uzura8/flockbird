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
	 * Api get_list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_list($parent_id = null)
	{
		$response = '';
		try
		{
			if ($this->format != 'html') throw new \HttpNotFoundException();

			$album_image_id = (int)$parent_id ?: (int)\Input::get('id');
			if (!$album_image_id || !$album_image = Model_AlbumImage::check_authority($album_image_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($album_image->public_flag, $album_image->album->member_id);

			list($limit, $params, $is_desc, $class_id) = $this->common_get_list_params();
			list($comments, $is_all_records) = Model_AlbumImageComment::get_comments($album_image_id, $limit, $params, $is_desc);
			$data = array(
				'comments' => $comments,
				'parent' => $album_image->album,
				'is_all_records' => $is_all_records,
				'list_more_box_attrs' => array('data-parent_id' => $album_image_id),
				'class_id' => $class_id,
				'delete_uri' => 'album/image/comment/api/delete.json',
				'list_more_box_attrs' => array(
					'data-uri' => 'album/image/comment/api/list/'.$album_image_id.'.html',
					'data-is_before' => true,
				),
			);
			if ($limit) $data['show_more_link'] = true;
			$response = \View::forge('_parts/comment/list', $data);
			$status_code = 200;

			return \Response::forge($response, $status_code);
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
	 * Api post_create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_create($parent_id = null)
	{
		$response = array('status' => 0);
		try
		{
			if ($this->format != 'json') throw new \HttpNotFoundException();
			\Util_security::check_csrf();

			$album_image_id = (int)$parent_id ?: (int)\Input::post('id');
			if (!$album_image_id || !$album_image = Model_AlbumImage::check_authority($album_image_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($album_image->public_flag, $album_image->album->member_id);

			// Lazy validation
			$body = trim(\Input::post('body', ''));
			if (!strlen($body)) throw new \HttpInvalidInputException;

			// Create a new comment
			$values = array(
				'body' => $body,
				'album_image_id' => $album_image_id,
				'member_id' => $this->u->id,
			);

			$comment = new Model_AlbumImageComment($values);
			\DB::start_transaction();
			$comment->save();
			\DB::commit_transaction();

			$response['status'] = 1;
			$response['id'] = $comment->id;
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
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album image comment delete
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
			if (!$id || !$album_image_comment = Model_AlbumImageComment::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}

			\DB::start_transaction();
			$album_image_comment->delete();
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
}
