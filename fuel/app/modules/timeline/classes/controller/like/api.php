<?php
namespace Timeline;

class Controller_Like_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_member'
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Timeline like get member
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_member($parent_id = null)
	{
		$response = '';
		try
		{
			if ($this->format != 'json') throw new \HttpNotFoundException();
			$timeline_id = (int)$parent_id;
			if (!$timeline_id || !$timeline = Model_timeline::check_authority($timeline_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($timeline->public_flag, $timeline->member_id);

			list($limit, $params, $is_desc, $class_id) = $this->common_get_list_params(array('desc' => 1), conf('view_params_default.list.limit.limit_max'));
			list($list, $is_all_records, $all_records_count) = Model_TimelineLike::get_list(array('timeline_id' => $timeline_id), $limit, 'member', true, $is_desc);

			$response = array(
				'status' => 1,
				'list' => $list,
				'is_all_records' => $is_all_records,
				'all_records_count' => $all_records_count,
			);
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Timeline like post update
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function post_update($id = null)
	{
		$response = array('status' => 0);
		try
		{
			if (!conf('like.isEnabled')) throw new \HttpNotFoundException();
			if ($this->format != 'json') throw new \HttpNotFoundException();
			\Util_security::check_csrf();

			$timeline_id = (int)$id;
			if (\Input::post('id')) $timeline_id = (int)\Input::post('id');
			if (!$timeline_id || !$timeline = Model_Timeline::check_authority($timeline_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($timeline->public_flag, $timeline->member_id);

			\DB::start_transaction();
			$is_liked = (bool)Model_TimelineLike::change_registered_status4unique_key(array(
				'timeline_id' => $timeline->id,
				'member_id' => $timeline->member_id
			));
			\DB::commit_transaction();

			$response['status'] = (int)$is_liked;
			$response['count'] = Model_TimelineLike::get_count4timeline_id($timeline->id);
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
