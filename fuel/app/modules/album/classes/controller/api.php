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
	 * @return  Response (html)
	 */
	public function get_list()
	{
		if ($this->format != 'html') throw new \HttpNotFoundException();

		$page      = (int)\Input::get('page', 1);
		$member_id = (int)\Input::get('member_id', 0);
		$response = '';
		try
		{
			$params = array(
				'related' => 'member',
				'limit' => \Config::get('album.article_list.limit'),
				'order_by' => array('created_at' => 'desc'),
			);
			if ($member_id) $params['where'] = array('member_id', $member_id);
			$data = \Site_Model::get_simple_pager_list('album', $page, $params, 'Album');

			$response = \View::forge('_parts/list', $data);
			$status_code = 200;
			return \Response::forge($response, $status_code);
		}
		catch(\Exception $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete()
	{
		$response = array('status' => 0);
		try
		{
			$this->auth_check_api();
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$album = Model_Album::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}

			\DB::start_transaction();
			Model_Album::delete_all($id);
			\Model_Member::recalculate_filesize_total($this->u->id);
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
		}
		catch(\FuelException $e)
		{
			\DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
