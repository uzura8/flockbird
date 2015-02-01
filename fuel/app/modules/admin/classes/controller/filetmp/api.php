<?php
namespace Admin;

class Controller_FileTmp_Api extends Controller_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Api get_upload
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_upload()
	{
		$this->common_FileTmp_get_upload();
	}

	/**
	 * Api post_upload
	 * 
	 * @access  public
	 * @return  Response (html or json)
	 */
	public function post_upload($type = null)
	{
		if (!$type || !in_array($type, array('img', 'file'))) $type = 'img';

		return $this->common_FileTmp_post_upload($type);
	}

	/**
	 * Api delete_upload
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function delete_upload()
	{
		return $this->common_FileTmp_delete_upload();
	}
}
