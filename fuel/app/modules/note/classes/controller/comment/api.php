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
			if ($this->format != 'html') throw new \HttpNotFoundException();
			$note_id = (int)$parent_id;
			if (!$note_id || !$note = Model_Note::check_authority($note_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($note->public_flag, $note->member_id);

			list($limit, $params, $is_desc, $class_id) = $this->common_get_list_params();
			list($comments, $is_all_records) = Model_NoteComment::get_comments($note_id, $limit, $params, $is_desc);
			$data = array(
				'comments' => $comments,
				'parent' => $note,
				'is_all_records' => $is_all_records,
				'list_more_box_attrs' => array(
					'id' => 'listMoreBox_comment_'.$note->id,
					'data-uri' => sprintf('note/comment/api/list/%s.html', $note->id),
					'data-list' => '#comment_list_'.$note->id,
					'data-is_before' => 1,
				),
				'class_id' => $class_id,
				'delete_uri' => 'note/comment/api/delete.json',
				'counter_selector' => '#comment_count_'.$note->id,
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

			$note_id = (int)$parent_id ?: (int)\Input::post('id');
			if (!$note_id || !$note = Model_Note::check_authority($note_id))
			{
				throw new \HttpNotFoundException;
			}
			$this->check_public_flag($note->public_flag, $note->member_id);

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
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');
			if (!$id || !$note_comment = Model_NoteComment::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}

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
