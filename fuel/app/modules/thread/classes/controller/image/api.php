<?php
namespace Thread;

class Controller_Image_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Delete thread image
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     Controller_Base::api_delete_common
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('thread_image', $id, null, t('site.image'), 'thread');
	}
}
