<?php
namespace Thread;

class Controller_Like_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_member',
	);

	/**
	 * Thread api like update
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

			$thread_id = (int)$id;
			if (\Input::post('id')) $thread_id = (int)\Input::post('id');
			$thread = Model_Thread::check_authority($thread_id);
			$this->check_browse_authority($thread->public_flag, $thread->member_id);

			\DB::start_transaction();
			$is_liked = (bool)Model_ThreadLike::change_registered_status4unique_key(array(
				'thread_id' => $thread->id,
				'member_id' => $this->u->id
			));
			\DB::commit_transaction();

			$response['status'] = (int)$is_liked;
			$response['count'] = Model_ThreadLike::get_count4thread_id($thread->id);
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
			$status_code = 500;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Thread like get member
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_member($parent_id = null)
	{
		$result = $this->get_liked_member_list(
			'\Thread\Model_ThreadLike',
			'\Thread\Model_Thread',
			$parent_id,
			'thread_id',
			\Site_Util::get_api_uri_get_liked_members('thread', $parent_id)
		);
		if ($result) return $result;
	}
}
