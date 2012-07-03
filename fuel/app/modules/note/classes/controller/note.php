<?php
namespace Note;

class Controller_Note extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'list_member',
		'detail',
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
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
//		$this->template->title = \Config::get('site.term.note');
//		$this->template->header_title = site_title();
//		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', \Config::get('site.term.note') => '');
//
//		$this->template->content = \View::forge('index');
	}

	/**
	 * Note list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$this->template->title = sprintf('最新の%s一覧', \Config::get('site.term.note'));
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', $this->template->title => '');

		$list = Model_Note::find()->related('member')->order_by('created_at', 'desc')->get();

		$this->template->content = \View::forge('_parts/list', array('list' => $list));
	}

	/**
	 * Note member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member()
	{
		$this->template->title = sprintf('自分の%s一覧', \Config::get('site.term.note'));
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(
			\Config::get('site.term.toppage') => '/',
			\Config::get('site.term.myhome') => '/member/',
			$this->template->title => '',
		);

		$list = Model_Note::find()->where('member_id', $this->current_user->id)->order_by('created_at', 'desc')->get();
		// paging 未実装, limit数:  Config::get('note.article_list.limit')

		$this->template->subtitle = \View::forge('_parts/member_subtitle');
		$this->template->content = \View::forge('_parts/list', array('member' => $this->current_user, 'list' => $list));
	}

	/**
	 * Note list_member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_list_member($member_id = null)
	{
		if (!$member = \Model_Member::check_authority($member_id))
		{
			throw new \HttpNotFoundException;
		}

		$this->template->title = sprintf('%sさんの%s一覧', $member->name, \Config::get('site.term.note'));
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', $this->template->title => '');

		$list = Model_Note::find()->where('member_id', $member_id)->order_by('created_at', 'desc')->get();

		$this->template->content = \View::forge('_parts/list', array('member' => $member, 'list' => $list));
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
		$comments = Model_NoteComment::find()->where('note_id', $id)->related('member')->order_by('created_at')->get();

		$this->template->title = trim($note->title);
		$this->template->header_title = site_title(mb_strimwidth($this->template->title, 0, 50, '...'));

		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/');
		if (\Auth::check() && $note->member_id == $this->current_user->id)
		{
			$this->template->breadcrumbs[\Config::get('site.term.myhome')] = '/member/';
			$key = '自分の'.\Config::get('site.term.note').'一覧';
			$this->template->breadcrumbs[$key] =  '/member/note/';
		}
		else
		{
			$this->template->breadcrumbs[\Config::get('site.term.note')] = '/note/';
			$key = $note->member->name.'さんの'.\Config::get('site.term.note').'一覧';
			$this->template->breadcrumbs[$key] =  '/note/list/'.$note->member->id;
		}
		$this->template->breadcrumbs[\Config::get('site.term.note').'詳細'] = '';

		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('note' => $note));
		$this->template->content = \View::forge('detail', array('note' => $note, 'comments' => $comments));
	}

	/**
	 * Note create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$form = $this->form();

		if (\Input::method() == 'POST')
		{
			$val = $form->validation();
			if ($val->run())
			{
				\Util_security::check_csrf();

				$post = $val->validated();
				$note = Model_Note::forge(array(
					'title' => $post['title'],
					'body'  => $post['body'],
					'member_id' => $this->current_user->id,
				));

				if ($note and $note->save())
				{
					\Session::set_flash('message', \Config::get('site.term.note').'を作成しました。');
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

		$this->template->title = \Config::get('site.term.note')."を書く";
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(
			\Config::get('site.term.toppage') => '/',
			\Config::get('site.term.note') => '/note/',
			$this->template->title => '',
		);
		$data = array('form' => $form);
		$this->template->content = \View::forge('create', $data);
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
		if (!$note = Model_Note::check_authority($id, $this->current_user->id))
		{
			throw new \HttpNotFoundException;
		}

		$form = $this->form();

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
					\Session::set_flash('message', \Config::get('site.term.note').'を編集をしました。');
					\Response::redirect('note/detail/'.$note->id);
				}
				else
				{
					Session::set_flash('error', 'Could not save.');
				}
			}
			else
			{
				Session::set_flash('error', $val->show_errors());
			}
			$form->repopulate();
		}
		else
		{
			$form->populate($note);
		}

		$this->template->title = \Config::get('site.term.note').'を編集する';
		$this->template->header_title = site_title($this->template->title);
		$this->template->breadcrumbs = array(
			\Config::get('site.term.toppage') => '/',
			\Config::get('site.term.myhome') => '/member/',
			'自分の'.\Config::get('site.term.note').'一覧' => '/member/note/',
			\Config::get('site.term.note').'詳細' => '/note/detail/'.$id,
			$this->template->title => '',
		);

		$data = array('form' => $form);
		$this->template->content = \View::forge('edit', $data);
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

		if (!$note = Model_Note::check_authority($id, $this->current_user->id))
		{
			throw new \HttpNotFoundException;
		}
		$note->delete();

		\Session::set_flash('message', \Config::get('site.term.note').'を削除しました。');
		\Response::redirect('note/member');
	}

	protected function form()
	{
		$form = \Fieldset::forge('', array('class' => 'form-horizontal'));

		$form->add('title', 'タイトル', array('class' => 'input-xlarge'))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('max_length', 255);

		$form->add('body', '本文', array('type' => 'textarea', 'cols' => 60, 'rows' => 10, 'class' => 'input-xlarge'))
			->add_rule('required');

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Security::fetch_token()));

		return $form;
	}
}
