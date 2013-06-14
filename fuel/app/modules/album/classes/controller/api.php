<?php
namespace Album;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list'
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Api list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_list()
	{
		$page      = (int)\Input::get('page', 1);
		$member_id = (int)\Input::get('member_id', 0);
		$response = '';
		try
		{
			$data = Site_Album::get_album_list($page, $member_id);
			$response = \View::forge('_parts/list.php', $data);
			//$response = 'fuga';
			$status_code = 200;
		}
		catch(\Exception $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
