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
		$result = $this->get_comment_list(
			'\Album\Model_AlbumImageComment',
			'\Album\Model_AlbumImage',
			$parent_id,
			'album_image_id',
			'album/image',
			\Config::get('album.articles.comment.limit'),
			\Config::get('album.articles.comment.limit_max'),
			array('album' => 'member_id')
		);
		if ($result) return $result;
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
			$album_image = Model_AlbumImage::check_authority($album_image_id);
			$this->check_browse_authority($album_image->public_flag, $album_image->album->member_id);

			// Lazy validation
			$body = trim(\Input::post('body', ''));
			if (!strlen($body)) throw new \HttpInvalidInputException;

			\DB::start_transaction();
			// Create a new comment
			$comment = new Model_AlbumImageComment(array(
				'body' => $body,
				'album_image_id' => $album_image_id,
				'member_id' => $this->u->id,
			));
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
	public function post_delete($id = null)
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');

			\DB::start_transaction();
			$album_image_comment = Model_AlbumImageComment::check_authority($id, $this->u->id);
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
