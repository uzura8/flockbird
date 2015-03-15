<?php
namespace Admin;

class Controller_News_File_Api extends Controller_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Delete news file
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('news_file', $id, null, term('site.file'));
	}
}
