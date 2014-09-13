<?php
namespace Note;

class Controller_Like_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_member',
	);

	/**
	 * Note api like update
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

			$note_id = (int)$id;
			if (\Input::post('id')) $note_id = (int)\Input::post('id');
			$note = Model_Note::check_authority($note_id);
			$this->check_browse_authority($note->public_flag, $note->member_id);

			\DB::start_transaction();
			$is_liked = (bool)Model_NoteLike::change_registered_status4unique_key(array(
				'note_id' => $note->id,
				'member_id' => $this->u->id
			));
			\DB::commit_transaction();

			$response['status'] = (int)$is_liked;
			$response['count'] = Model_NoteLike::get_count4note_id($note->id);
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
	 * Note like get member
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function get_member($parent_id = null)
	{
		$result = $this->get_liked_member_list(
			'\Note\Model_NoteLike',
			'\Note\Model_Note',
			$parent_id,
			'note_id',
			Site_Util::get_liked_member_api_uri($parent_id)
		);
		if ($result) return $result;
	}
}
