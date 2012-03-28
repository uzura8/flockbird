<?php
namespace Note;

class Controller_Comment extends \Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();

		$this->auth_check();
	}

	public function action_create($note_id = null)
	{
		if (!$note_id || !$note = Model_Note::find($note_id))
		{
			throw new \HttpNotFoundException;
		}

		// Lazy validation
		if (\Input::post('body'))
		{
			// Create a new comment
			$comment = new Model_NoteComment(array(
				'body' => \Input::post('body'),
				'note_id' => $note_id,
				'member_id' => $this->current_user->id,
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
		if (!$comment = Model_NoteComment::check_authority($id, $this->current_user->id))
		{
			throw new \HttpNotFoundException;
		}
		$note_id = $comment->note_id;
		$comment->delete();

		\Session::set_flash('message', \Config::get('site.term.note').'を削除しました。');
		\Response::redirect('note/detail/'.$note_id);
	}
}
