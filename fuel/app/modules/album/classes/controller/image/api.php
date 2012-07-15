<?php
namespace Album;

class Controller_Image_api extends \Controller_Rest
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();

		//$this->auth_check();
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
}
