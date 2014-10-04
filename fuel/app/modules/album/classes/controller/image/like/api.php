<?php
namespace Album;

class Controller_Image_Like_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_member',
	);

	/**
	 * AlbumImage api like update
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

			$album_image_id = (int)$id;
			if (\Input::post('id')) $album_image_id = (int)\Input::post('id');
			$album_image = Model_AlbumImage::check_authority($album_image_id);
			$this->check_browse_authority($album_image->public_flag, $album_image->album->member_id);

			\DB::start_transaction();
			$is_liked = (bool)Model_AlbumImageLike::change_registered_status4unique_key(array(
				'album_image_id' => $album_image->id,
				'member_id' => $this->u->id
			));
			\DB::commit_transaction();

			$response['status'] = (int)$is_liked;
			$response['count'] = Model_AlbumImageLike::get_count4album_image_id($album_image->id);
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
	 * AlbumImage like get member
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_member($parent_id = null)
	{
		$result = $this->get_liked_member_list(
			'\Album\Model_AlbumImageLike',
			'\Album\Model_AlbumImage',
			$parent_id,
			'album_image_id',
			\Site_Util::get_api_uri_get_liked_members('album_image', $parent_id),
			null,
			\Config::get('view_params_default.like.members.popover.limit'),
			\Config::get('view_params_default.like.members.popover.limit_max'),
			array('album' => 'member_id')
		);
		if ($result) return $result;
	}
}
