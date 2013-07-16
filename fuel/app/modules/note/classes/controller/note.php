<?php
namespace Note;

class Controller_Note extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'member',
		'detail',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Note index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Note list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$this->set_title_and_breadcrumbs(sprintf('最新の%s一覧', \Config::get('term.note')));
		$data = \Site_Model::get_simple_pager_list('note', 1, array(
			'related' => 'member',
			'order_by' => array('created_at' => 'desc'),
			'limit' => \Config::get('note.articles.limit'),
		), 'Note');
		$this->template->content = \View::forge('_parts/list', $data);
		$this->template->post_footer = \View::forge('_parts/list_footer');
	}

	/**
	 * Note member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		$member_id = (int)$member_id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id);
		$this->set_title_and_breadcrumbs(sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('term.note')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('_parts/member_subtitle') : '';

		$data = \Site_Model::get_simple_pager_list('note', 1, array(
			//'related' => 'member',
			'where' => array('member_id', $member->id),
			'limit' => \Config::get('note.articles.limit'),
			'order_by' => array('created_at' => 'desc'),
		), 'Note');
		$data['member'] = $member;
		$this->template->content = \View::forge('_parts/list', $data);
		$this->template->post_footer = \View::forge('_parts/list_footer');
	}

	/**
	 * Note detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		if (!$note = Model_Note::check_authority($id))
		{
			throw new \HttpNotFoundException;
		}
		$record_limit = (\Input::get('all_comment', 0))? 0 : \Config::get('site.record_limit.default.comment.m');

		$this->set_title_and_breadcrumbs($note->title, null, $note->member, 'note');
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('note' => $note));

		list($comments, $is_all_records) = Model_NoteComment::get_comments($id, $record_limit);
		$this->template->content = \View::forge('detail', array('note' => $note, 'comments' => $comments, 'is_all_records' => $is_all_records));
	}

	/**
	 * Note create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$note = Model_Note::forge();
		$form = \Site_Util::get_form_instance('note', $note, true);

		if (\Input::method() == 'POST')
		{
			$val = $form->validation();
			if ($val->run())
			{
				\Util_security::check_csrf();

				$post = $val->validated();
				$note->title = $post['title'];
				$note->body = $post['body'];
				$note->member_id = $this->u->id;

				if ($note and $note->save())
				{
					\Session::set_flash('message', \Config::get('term.note').'を作成しました。');
					\Response::redirect('note/detail/'.$note->id);
				}
				else
				{
					Session::set_flash('error', 'Could not save post.');
				}
			}
			else
			{
				Session::set_flash('error', $val->show_errors());
			}
		}

		$this->set_title_and_breadcrumbs(\Config::get('term.note').'を書く', null, $this->u, 'note');
		$this->template->content = \View::forge('create', array('form' => $form));
		$this->template->content->set_safe('html_form', $form->build('note/create'));// form の action に入る
	}

	/**
	 * Note edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		if (!$note = Model_Note::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}

		$form = \Site_Util::get_form_instance('note', $note, true);

		if (\Input::method() == 'POST')
		{
			$val = $form->validation();
			if ($val->run())
			{
				\Util_security::check_csrf();

				$post = $val->validated();
				$note->title = $post['title'];
				$note->body  = $post['body'];

				if ($note and $note->save())
				{
					\Session::set_flash('message', \Config::get('term.note').'を編集をしました。');
					\Response::redirect('note/detail/'.$note->id);
				}
				else
				{
					\Session::set_flash('error', 'Could not save.');
				}
			}
			else
			{
				\Session::set_flash('error', $val->show_errors());
			}
			$form->repopulate();
		}
		else
		{
			$form->populate($note);
		}

		$this->set_title_and_breadcrumbs(\Config::get('term.note').'を編集する', array('/note/'.$id => $note->title), $note->member, 'note');
		$this->template->content = \View::forge('edit', array('form' => $form));
		$this->template->content->set_safe('html_form', $form->build('note/edit/'.$id));// form の action に入る
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

		if (!$note = Model_Note::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}
		$note->delete();

		\Session::set_flash('message', \Config::get('term.note').'を削除しました。');
		\Response::redirect('note/member');
	}

	protected function form()
	{
		$form = \Site_Util::get_form_instance();

		$form->add('title', 'タイトル', array('class' => 'input-xlarge'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('max_length', 255);

		$form->add('body', '本文', array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'))
			->add_rule('required');

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Util_security::get_csrf()));

		return $form;
	}
}
