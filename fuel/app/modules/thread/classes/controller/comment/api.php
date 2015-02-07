<?php
namespace Thread;

class Controller_Comment_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Api get_list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_list($parent_id = null)
	{
		$result = $this->get_comment_list(
			'\Thread\Model_ThreadComment',
			'\Thread\Model_Thread',
			$parent_id,
			'thread_id',
			'thread'
		);
		if ($result) return $result;
	}

	/**
	 * Api post_create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_create($parent_id = null)
	{
		$response = array('status' => 0);
		try
		{
			$this->check_response_format('json');
			\Util_security::check_csrf();

			$thread_id = (int)$parent_id;
			if (\Input::post('id')) $thread_id = (int)\Input::post('id');
			$thread = Model_Thread::check_authority($thread_id);
			$this->check_browse_authority($thread->public_flag, $thread->member_id);

			// Lazy validation
			$body = trim(\Input::post('body', ''));
			if (!strlen($body)) throw new \HttpInvalidInputException;

			\DB::start_transaction();
			// Create a new comment
			$comment = new Model_ThreadComment(array(
				'body' => $body,
				'thread_id' => $thread_id,
				'member_id' => $this->u->id,
			));
			$comment->save();
			\DB::commit_transaction();

			$response['status'] = 1;
			$response['id'] = $comment->id;
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

	/**
	 * Api comment delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete($id = null)
	{
		$response = array('status' => 0);
		try
		{
			$this->check_response_format('json');
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');
			$thread_comment = Model_ThreadComment::check_authority($id);

			\DB::start_transaction();
			$thread_comment->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
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
