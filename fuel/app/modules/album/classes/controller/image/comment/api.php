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
		if ($this->format != 'html') throw new \HttpNotFoundException();

		$album_image_id = (int)$parent_id;
		$before_id      = (int)\Input::get('before_id', 0);
		$after_id       = (int)\Input::get('after_id', 0);
		$is_desc        = (bool)\Input::get('is_desc', false);
		$show_more_link = (bool)\Input::get('disp_more', 1);
		$limit     = (int)\Input::get('limit', \Config::get('site.view_params_default.list.comment.limit'));
		if (\Input::get('limit') == 'all') $limit = \Config::get('site.view_params_default.list.comment.max_limit', 50);

		$response = '';
		try
		{
			if (!$album_image_id || !$album_image = Model_AlbumImage::check_authority($album_image_id))
			{
				throw new \HttpNotFoundException;
			}

			$params = array();
			if ($before_id) $params[] = array('id', '>', $before_id);
			if ($after_id)  $params[] = array('id', '<', $after_id);
			list($comments, $is_all_records) = Model_AlbumImageComment::get_comments($album_image_id, $limit, $params, $is_desc);

			$data = array(
				'comments' => $comments,
				'delete_uri' => 'album/image/comment/api/delete.json',
				'parent' => $album_image->album,
				'is_all_records' => $is_all_records,
				'list_more_box_attrs' => array('data-parent_id' => $album_image_id),
			);
			if ($limit) $data['show_more_link'] = true;
			$response = \View::forge('_parts/comment/list', $data);
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
		catch(\FuelException $e)
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
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
