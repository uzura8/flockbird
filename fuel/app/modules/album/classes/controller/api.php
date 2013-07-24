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
			$data = \Site_Model::get_simple_pager_list('album', $page, array(
				'related'  => 'member',
				'where'    => \Site_Model::get_where_params4list($member_id, \Auth::check() ? $this->u->id : 0, $this->check_is_mypage($member_id)),
				'limit'    => \Config::get('album.articles.limit'),
				'order_by' => array('created_at' => 'desc'),
			), 'Album');

			$response = \View::forge('_parts/list', $data);
			$status_code = 200;
			return \Response::forge($response, $status_code);
		}
		catch(\FuelException $e)
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
		catch(\FuelException $e)
		{
			\DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album update public_flag
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function post_update_public_flag()
	{
		if ($this->format != 'html') throw new \HttpNotFoundException();
		$response = '0';
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$album = Model_Album::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			list($public_flag, $model) = \Site_Util::validate_params_public_flag($album->public_flag);

			\DB::start_transaction();
			$album->public_flag = $public_flag;
			$album->save();
			\DB::commit_transaction();

			$response = \View::forge('_parts/public_flag_selecter', array('model' => $model, 'id' => $id, 'public_flag' => $public_flag, 'is_mycontents' => true));
			$status_code = 200;

			return \Response::forge($response, $status_code);
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(\FuelException $e)
		{
			\DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
