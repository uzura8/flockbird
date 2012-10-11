<?php
namespace Album;

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
	 * Api index
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
		$comments = Model_AlbumImageComment::find()->where('album_image_id', $id)->related('member')->order_by('id')->get();

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
		$response = array();
		$status_code = 200;
		try
		{
			\Util_security::check_csrf();
			$id = (int)\Input::post('id');

			if (!$id || !$album_image = Model_AlbumImage::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			$album_image->album->cover_album_image_id = $id;
			$album_image->album->save();

			$response['status'] = 'OK';
			$response['album_id'] = $album_image->album_id;
		}
		catch(Exception $e)
		{
			$response['status'] = 'NG';
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
