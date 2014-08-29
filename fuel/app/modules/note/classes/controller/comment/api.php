<?php
namespace Note;

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
			$this->check_response_format(array('json', 'html'));

			$parent_id = (int)$parent_id;
			$parent_obj = Model_Note::check_authority($parent_id);
			$this->check_browse_authority($parent_obj->public_flag, $parent_obj->member_id);

			list($limit, $is_latest, $is_desc, $since_id, $max_id) = $this->common_get_list_params();
			list($list, $next_id, $all_comment_count)
				= Model_NoteComment::get_list(array('note_id' => $parent_id), $limit, $is_latest, $is_desc, $since_id, $max_id, null, false, ($this->format == 'json'));

			$status_code = 200;
			if ($this->format == 'html')
			{
				// html response
				return \Response::forge(\View::forge('_parts/comment/list', array(
					'list' => $list,
					'next_id' => $next_id,
					'parent' => $parent_obj,
					'list_more_box_attrs' => array(
						'id' => 'listMoreBox_comment_'.$parent_id,
						'data-uri' => sprintf('note/comment/api/list/%s.html', $parent_id),
						'data-list' => '#comment_list_'.$parent_id,
						'data-max_id' => $max_id,
						//'data-prepend' => 1,
					),
					'delete_uri' => 'note/comment/api/delete.json',
					'counter_selector' => '#comment_count_'.$parent_id,
				)), $status_code);
			}

			// json response
			$response = array(
				'status' => 1,
				'list' => $list,
				'next_id' => $next_id,
			);
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
			$this->check_response_format('json');
			\Util_security::check_csrf();

			$note_id = (int)$parent_id;
			if (\Input::post('id')) $note_id = (int)\Input::post('id');
			$note = Model_Note::check_authority($note_id);
			$this->check_browse_authority($note->public_flag, $note->member_id);

			// Lazy validation
			$body = trim(\Input::post('body', ''));
			if (!strlen($body)) throw new \HttpInvalidInputException;

			// Create a new comment
			$values = array(
				'body' => $body,
				'note_id' => $note_id,
				'member_id' => $this->u->id,
			);

			\DB::start_transaction();
			$comment = Model_NoteComment::save_comment($note_id, $this->u->id, $body);
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
			$note_comment = Model_NoteComment::check_authority($id);

			\DB::start_transaction();
			$note_comment->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
