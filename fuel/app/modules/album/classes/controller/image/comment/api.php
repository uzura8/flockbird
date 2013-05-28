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
			$album_image_id = (int)$parent_id;
			$before_id      = (int)\Input::get('before_id', 0);
			$limit          = (int)\Input::get('limit', 0);

			if (!$album_image_id || !$album_image = Model_AlbumImage::check_authority($album_image_id))
			{
				throw new \HttpNotFoundException;
			}

			$query = Model_AlbumImageComment::find()->where('album_image_id', $album_image_id)->related('member');
			if ($before_id) $query = $query->where('id', '>', $before_id);
			if ($limit)     $query = $query->limit($limit);
			$comments = $query->order_by('id')->get();

			$response = \View::forge('image/comment/_parts/list.php', array('comments' => $comments, 'album_image' => $album_image));
			$status_code = 200;
		}
		catch(\Exception $e)
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
			$comment->save();

			$response['status'] = 1;
			$response['id'] = $comment->id;
			$status_code = 200;
		}
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
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

			$album_image_comment->delete();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
		}
		catch(\Exception $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
