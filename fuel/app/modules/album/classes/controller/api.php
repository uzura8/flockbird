<?php
namespace Album;

class Controller_Api extends \Controller_Rest
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
	public function get_detail($id = null)
	{
		$id = (int)$id;
		if (!$id || !$album = Model_Album::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}
		$album_images = Model_AlbumImage::find()->where('album_id', $id)->related('album')->related('file')->order_by('id')->get();

		$this->response($album_images);
	}
}
