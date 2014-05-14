<?php
namespace Note;

class Controller_Comment extends \Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	public function action_create($note_id = null)
	{
		if (!$note_id || !$note = Model_Note::find($note_id))
		{
			throw new \HttpNotFoundException;
		}
		$this->check_public_flag($note->public_flag, $note->member_id);

		// Lazy validation
		if (\Input::post('body'))
		{
			\Util_security::check_csrf();

			// Create a new comment
			$comment = new Model_NoteComment(array(
				'body' => \Input::post('body'),
				'note_id' => $note_id,
				'member_id' => $this->u->id,
			));

			// Save the post and the comment will save too
			if ($comment->save())
			{
				\Session::set_flash('message', 'コメントしました。');
			}
			else
			{
				\Session::set_flash('error', 'コメントに失敗しました。');
			}

			\Response::redirect('note/detail/'.$note_id);
		}
		else
		{
			Controller_Note::action_detail($note_id);
		}
	}

	/**
	 * Note delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_csrf(\Input::get(\Config::get('security.csrf_token_key')));

		if (!$comment = Model_NoteComment::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}

		$note_id = $comment->note_id;
		$comment->delete();

		\Session::set_flash('message', term('note').'を削除しました。');
		\Response::redirect('note/detail/'.$note_id);
	}
}
