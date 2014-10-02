<?php
namespace Album;

class Controller_Image_Comment_Like_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_member',
	);

	/**
	 * AlbumImageComment api like update
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function post_update($id = null)
	{
		$response = array('status' => 0);
		try
		{
			if (!conf('like.isEnabled')) throw new \HttpNotFoundException();
			$this->check_response_format('json');
			\Util_security::check_csrf();

			$album_image_comment_id = (int)$id;
			if (\Input::post('id')) $album_image_comment_id = (int)\Input::post('id');
			$album_image_comment = Model_AlbumImageComment::check_authority($album_image_comment_id);
			$this->check_browse_authority($album_image_comment->album_image->public_flag, $album_image_comment->member_id);

			\DB::start_transaction();
			$is_liked = (bool)Model_AlbumImageCommentLike::change_registered_status4unique_key(array(
				'album_image_comment_id' => $album_image_comment->id,
				'member_id' => $this->u->id
			));
			\DB::commit_transaction();

			$response['status'] = (int)$is_liked;
			$response['count'] = Model_AlbumImageCommentLike::get_count4album_image_comment_id($album_image_comment->id);
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

	/**
	 * AlbumImageComment like get member
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_member($parent_id = null)
	{
		$result = $this->get_liked_member_list(
			'\Album\Model_AlbumImageCommentLike',
			'\Album\Model_AlbumImageComment',
			$parent_id,
			'album_image_comment_id',
			\Site_Util::get_api_uri_get_liked_members('album/image/comment', $parent_id),
			'album_image'
		);
		if ($result) return $result;
	}
}
