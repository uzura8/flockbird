<?php
namespace Album;

class Controller_Image_Comment_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Api post_create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_create()
	{
		$response = array('status' => 0);
		try
		{
			$this->auth_check_api();
			\Util_security::check_csrf();

			$album_image_id = (int)\Input::post('id');
			if (!$album_image_id || !$album_image = Model_AlbumImage::check_authority($album_image_id))
			{
				throw new \HttpNotFoundException;
			}

			// Lazy validation
			if (!\Input::post('body')) throw new \HttpNotFoundException;

			// Create a new comment
			$values = array(
				'body' => \Input::post('body'),
				'album_image_id' => $album_image_id,
				'member_id' => $this->u->id,
			);

			$comment = new Model_AlbumImageComment($values);
			if (!$comment->save()) throw new \HttpServerErrorException;

			$response['status'] = 1;
			$response['id'] = $comment->id;
			$status_code = 200;
		}
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
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
			$this->auth_check_api();
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$album_image_comment = Model_AlbumImageComment::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}

			if (!$album_image_comment->delete()) throw new \HttpServerErrorException;

			$response['status'] = 1;
			$response['id'] = $album_image_comment->id;
			$status_code = 200;
		}
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
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
}
