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
			$note_id = (int)$parent_id;
			$before_id      = (int)\Input::get('before_id', 0);
			$after_id       = (int)\Input::get('after_id', 0);
			$limit          = (int)\Input::get('limit', 0);
			$is_desc        = (bool)\Input::get('is_desc', false);

			if (!$note_id || !$note = Model_Note::check_authority($note_id))
			{
				throw new \HttpNotFoundException;
			}

			$params = array();
			if ($before_id) $params[] = array('id', '>', $before_id);
			if ($after_id)  $params[] = array('id', '<', $after_id);
			list($comments, $is_all_records) = Model_NoteComment::get_comments($note_id, $limit, $params, $is_desc);

			$data = array('comments' => $comments, 'parent' => $note, 'is_all_records' => $is_all_records);
			if ($limit) $data['show_more_link'] = true;
			$response = \View::forge('_parts/comment/list.php', $data);
			$status_code = 200;
		}
		catch(\Exception $e)
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
			$this->auth_check_api();
			\Util_security::check_csrf();

			$note_id = (int)\Input::post('id');
			if (!$note_id || !$note = Model_Note::check_authority($note_id))
			{
				throw new \HttpNotFoundException;
			}

			// Lazy validation
			if (!\Input::post('body')) throw new \HttpNotFoundException;

			// Create a new comment
			$values = array(
				'body' => \Input::post('body'),
				'note_id' => $note_id,
				'member_id' => $this->u->id,
			);

			$comment = new Model_NoteComment($values);
			$comment->save();

			$response['status'] = 1;
			$response['id'] = $comment->id;
			$status_code = 200;
		}
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
		}
		catch(\Exception $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album image comment delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete()
	{
		$response = array('status' => 0);
		try
		{
			$this->auth_check_api();
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$note_comment = Model_NoteComment::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}

			$note_comment->delete();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\SiteApiNotAuthorizedException $e)
		{
			$status_code = 401;
		}
		catch(\Exception $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}
