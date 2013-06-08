<?php
namespace Album;

class Controller_Image_comment extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'list',
	);

	public function before()
	{
		parent::before();
	}

	public function action_create($album_image_id = null)
	{
		if (!$album_image_id || !$album_image = Model_AlbumImage::find($album_image_id))
		{
			throw new \HttpNotFoundException;
		}

		// Lazy validation
		if (\Input::post('body'))
		{
			\Util_security::check_csrf();

			// Create a new comment
			$comment = new Model_AlbumImageComment(array(
				'body' => \Input::post('body'),
				'album_image_id' => $album_image_id,
				'member_id' => $this->u->id,
			));

			// Save the post and the comment will save too
			if ($comment->save())
			{
				\Session::set_flash('message', 'コメントしました。');
			}
			else
			{
				\Session::set_flash('error', 'コメントに失敗しました。');
			}

			\Response::redirect('album/image/'.$album_image_id);
		}
		else
		{
			Controller_Image::action_detail($album_image_id);
		}
	}

	public function action_create_ajax($album_image_id = null)
	{
		if (!$album_image_id || !$album_image = Model_AlbumImage::find($album_image_id))
		{
			throw new \HttpNotFoundException;
		}
		// Lazy validation
		if (!\Input::post('body'))	throw new \HttpNotFoundException;
		// if (!\Security::check_token()) throw new HttpServerErrorException;

		// Create a new comment
		$values = array(
			'body' => \Input::post('body'),
			'album_image_id' => $album_image_id,
			'member_id' => $this->u->id,
		);

		$comment = new Model_AlbumImageComment($values);
		// Save the post and the comment will save too
		if (!$comment->save()) throw new HttpServerErrorException;

		// Get the active response object
		$response = \Request::active()->controller_instance->response;
		if (\Input::is_ajax())
		{
			$response->set_header('Content-type', 'application/json');
		}

		return $response->body(json_encode(array('result' => 'success')));
	}

	public function action_list($album_image_id = null)
	{
		$album_image_id = (int)$album_image_id;
		if (!$album_image_id || !$album_image = Model_Albumimage::check_authority($album_image_id))
		{
			return \View::forge('image/comment/list_error.php');
		}

		return \View::forge('image/comment/_parts/list.php', array('album_image' => $album_image));
	}

	/**
	 * Album image comment delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		$id = (int)$id;
		if (!$id || !$album_image_comment = Model_AlbumImageComment::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		\Util_security::check_csrf(\Input::get(\Config::get('security.csrf_token_key')));

		$album_image_id = $album_image_comment->album_image_id;
		$album_image_comment->delete();

		\Session::set_flash('message', \Config::get('site.term.note').'を削除しました。');
		\Response::redirect('album/image/'.$album_image_id);
	}

	/**
	 * Album image comment delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete_ajax($id = null)
	{
		$id = (int)$id;
		if (!$id || !$album_image_comment = Model_AlbumImageComment::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		\Util_security::check_csrf(\Input::get(\Config::get('security.csrf_token_key')));

		if (!$album_image_comment->delete()) throw new \HttpNotFoundException;

		// Get the active response object
		$response = \Request::active()->controller_instance->response;
		if (\Input::is_ajax())
		{
			$response->set_header('Content-type', 'application/json');
		}

		return $response->body(json_encode(array('result' => 'success')));
	}
}
