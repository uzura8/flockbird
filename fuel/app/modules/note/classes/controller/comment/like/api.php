<?php
namespace Note;

class Controller_Comment_Like_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_member',
	);

	/**
	 * NoteComment api like update
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

			$note_comment_id = (int)$id;
			if (\Input::post('id')) $note_comment_id = (int)\Input::post('id');
			$note_comment = Model_NoteComment::check_authority($note_comment_id);
			$this->check_browse_authority($note_comment->note->public_flag, $note_comment->member_id);

			\DB::start_transaction();
			$is_liked = (bool)Model_NoteCommentLike::change_registered_status4unique_key(array(
				'note_comment_id' => $note_comment->id,
				'member_id' => $this->u->id
			));
			\DB::commit_transaction();

			$response['status'] = (int)$is_liked;
			$response['count'] = Model_NoteCommentLike::get_count4note_comment_id($note_comment->id);
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
	 * NoteComment like get member
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_member($parent_id = null)
	{
		$result = $this->get_liked_member_list(
			'\Note\Model_NoteCommentLike',
			'\Note\Model_NoteComment',
			$parent_id,
			'note_comment_id',
			\Site_Util::get_api_uri_get_liked_members('note/comment', $parent_id),
			'note'
		);
		if ($result) return $result;
	}
}
