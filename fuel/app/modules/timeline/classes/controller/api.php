<?php
namespace Timeline;

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

		$last_id   = (int)\Input::get('last_id', 0);
		$member_id = (int)\Input::get('member_id', 0);
		$limit     = (int)\Input::get('limit', 0);
		$is_over   = (bool)\Input::get('is_over', 0);
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id);

		if ($limit > \Config::get('timeline.articles.max_limit')) $limit = \Config::get('timeline.articles.max_limit');
		$response = '';
		try
		{
			$params = array(
				'where'    => \Site_Model::get_where_params4list(
					$member_id,
					\Auth::check() ? $this->u->id : 0,
					$is_mypage
				),
				'order_by' => array('updated_at' => 'desc'),
			);
			//if ($limit) $params['limit'] = \Config::get('timeline.articles.limit');
			if ($limit) $params['limit'] = $limit;
			list($list, $is_next) = \Site_Model::get_pager_list('timeline', $last_id, $params, 'Timeline', true, $is_over);

			$response = \View::forge('_parts/timeline/list', array('list' => $list, 'is_next' => $is_next));
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
	 * Api post_create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_create()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$timeline_data = Model_TimelineData::forge();
			$val = \Validation::forge();
			$val->add_model($timeline_data);
			$val->add('public_flag', \Config::get('term.public_flag.label'))->add_rule('public_flag');
			if (!$val->run()) throw new \FuelException($val->show_errors());
			$post = $val->validated();

			\DB::start_transaction();
			$timeline = Model_Timeline::forge();
			$timeline->member_id = $this->u->id;
			$timeline->public_flag = $post['public_flag'];
			$timeline->is_deleted = 0;
			$timeline->save();

			$timeline_data->timeline_id = $timeline->id;
			$timeline_data->member_id = $this->u->id;
			$timeline_data->body = $post['body'];
			$timeline_data->type = Site_Util::get_timeline_type(null, $post['body']);
			$timeline_data->save();
			\DB::commit_transaction();

			$response['status'] = 1;
			$response['id'] = $timeline->id;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
			$response['message'] = $e->getMessage();
		}

		$this->response($response, $status_code);
	}

	/**
	 * Timeline delete
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
			if (!$id || !$timeline = Model_Timeline::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			$timeline->delete();

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
	 * Timeline update public_flag
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
			if (!$id || !$timeline = Model_Timeline::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			list($public_flag, $model) = \Site_Util::validate_params_public_flag($timeline->public_flag);

			\DB::start_transaction();
			$timeline->public_flag = $public_flag;
			$timeline->save();
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
