<?php
namespace Timeline;

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
			'\Timeline\Model_TimelineComment',
			'\Timeline\Model_Timeline',
			$parent_id,
			'timeline_id',
			'timeline'
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

			$timeline_id = (int)$parent_id;
			if (\Input::post('id')) $timeline_id = (int)\Input::post('id');
			$timeline = Model_Timeline::check_authority($timeline_id);
			$this->check_browse_authority($timeline->public_flag, $timeline->member_id);

			// validation
			if (Site_Util::check_type_for_post_foreign_table_comment($timeline->type))
			{
				throw new \HttpInvalidInputException;
			}
			$body = trim(\Input::post('body', ''));
			if (!strlen($body)) throw new \HttpInvalidInputException;

			// Create a new comment
			$values = array(
				'body' => $body,
				'timeline_id' => $timeline_id,
				'member_id' => $this->u->id,
			);

			\DB::start_transaction();
			$comment = new Model_TimelineComment($values);
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
	 * Timeline comment delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete($id = null)
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');
			if (!$id || !$timeline_comment = Model_TimelineComment::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}

			$timeline_comment->delete();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
