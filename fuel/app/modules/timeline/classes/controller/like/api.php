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
		$result = $this->get_liked_member_list(
			'\Timeline\Model_TimelineLike',
			'\Timeline\Model_Timeline',
			$parent_id,
			'timeline_id',
			Site_Util::get_liked_member_api_uri($parent_id)
		);
		if ($result) return $result;
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
			$this->check_response_format('json');
			\Util_security::check_csrf();

			$timeline_id = (int)$id;
			if (\Input::post('id')) $timeline_id = (int)\Input::post('id');
			$timeline = Model_Timeline::check_authority($timeline_id);
			$this->check_browse_authority($timeline->public_flag, $timeline->member_id);

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
