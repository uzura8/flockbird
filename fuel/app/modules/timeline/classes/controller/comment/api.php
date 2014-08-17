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
		$response = '';
		try
		{
			if ($this->format != 'html') throw new \HttpNotFoundException();
			$timeline_id = (int)$parent_id;
			if (!$timeline_id || !$timeline = Model_timeline::check_authority($timeline_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($timeline->public_flag, $timeline->member_id);

			list($limit, $params, $is_desc, $class_id) = $this->common_get_list_params();
			list($comments, $is_all_records) = Model_TimelineComment::get_comments($timeline_id, $limit, $params, $is_desc);

			$data = array(
				'comments' => $comments,
				'parent' => $timeline,
				'is_all_records' => $is_all_records,
				'list_more_box_attrs' => array('id' => 'listMoreBox_comment_'.$timeline_id, 'data-parent_id' => $timeline_id),
				'class_id' => $class_id,
				'delete_uri' => Site_Util::get_comment_api_uri('delete', $timeline->type, $timeline->id, $timeline->foreign_id),
				'counter_selector' => '#comment_count_'.$timeline->id,
				'list_more_box_attrs' => array(
					'data-uri' => 'timeline/comment/api/list/'.$timeline->id.'.html',
					'data-is_before' => true,
					'data-list' => '#comment_list_'.$timeline->id,
					'data-is_before' => 1,
				),
			);
			if ($limit) $data['show_more_link'] = true;

			$response = \View::forge('_parts/comment/list', $data);
			$status_code = 200;
			return \Response::forge($response, $status_code);
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
			if ($this->format != 'json') throw new \HttpNotFoundException();
			\Util_security::check_csrf();

			$timeline_id = (int)$parent_id ?: (int)\Input::post('id');
			if (!$timeline_id || !$timeline = Model_Timeline::check_authority($timeline_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($timeline->public_flag, $timeline->member_id);

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
