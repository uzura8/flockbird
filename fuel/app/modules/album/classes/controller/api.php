<?php
namespace Album;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_detail',
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
