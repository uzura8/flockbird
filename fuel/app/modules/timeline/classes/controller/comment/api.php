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
		if ($this->format != 'html') throw new \HttpNotFoundException();

		$response = '';
		try
		{
			$timeline_id = (int)$parent_id;
			$before_id   = (int)\Input::get('before_id', 0);
			$after_id    = (int)\Input::get('after_id', 0);
			$limit       = (int)\Input::get('limit', 0);
			$is_desc     = (bool)\Input::get('is_desc', false);

			if (!$timeline_id || !$timeline = Model_timeline::check_authority($timeline_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($timeline->public_flag, $timeline->member_id);

			$params = array();
			if ($before_id) $params[] = array('id', '>', $before_id);
			if ($after_id)  $params[] = array('id', '<', $after_id);
			list($comments, $is_all_records) = Model_TimelineComment::get_comments($timeline_id, $limit, $params, $is_desc);

			$data = array('comments' => $comments, 'parent' => $timeline, 'is_all_records' => $is_all_records);
			if ($limit) $data['show_more_link'] = true;
			$response = \View::forge('_parts/comment/list', $data);
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

			$timeline_id = (int)\Input::post('id');
			if (!$timeline_id || !$timeline = Model_Timeline::check_authority($timeline_id, 0))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($timeline->public_flag, $timeline->member_id);

			// validation
			if (!Site_Util::check_accepted_type_for_post_comment($timeline->type, $timeline->foreign_table))
			{
				throw new \HttpInvalidInputException;
			}
			if (!$body = trim(\Input::post('body', ''))) throw new \HttpInvalidInputException;

			// Create a new comment
			$values = array(
				'body' => $body,
				'timeline_id' => $timeline_id,
				'member_id' => $this->u->id,
			);

			$comment = new Model_TimelineComment($values);
			$comment->save();

			$response['status'] = 1;
			$response['id'] = $comment->id;
			$status_code = 200;
		}
		catch(\HttpInvalidInputException $e)
		{
			$status_code = 400;
		}
		catch(\FuelException $e)
		{
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
	public function post_delete()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
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
