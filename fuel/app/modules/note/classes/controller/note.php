<?php
namespace Note;

class Controller_Note extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
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
		$this->template->title = \Config::get('site.term.note');
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(\Config::get('site.term.toppage') => '/', \Config::get('site.term.note') => '');

		$this->template->content = \View::forge('index');
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
		if (!$id) throw new HttpNotFoundException;

		//$post = Model_Note::find()->where('slug', $slug)->related('user')->related('comments')->get_one();
		$note = Model_Note::find()->where('id', $id)->related('member')->get_one();

		$this->template->title = sprintf('[%s] %s', \Config::get('site.term.note'), trim($note->title));
		$this->template->header_title = site_title(mb_strimwidth($this->template->title, 0, 50, '...'));
		$this->template->breadcrumbs = array(
			\Config::get('site.term.toppage') => '/',
			\Config::get('site.term.note') => '/note/',
			$this->current_user->name.'さんの'.\Config::get('site.term.note') => '/note/list/'.$this->current_user->id,
			\Config::get('site.term.note').'詳細' => '',
		);
		$this->template->content = \View::forge('detail', array('note' => $note));
	}

	/**
	 * Note create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create($id = null)
	{
		$form = $this->form();

		if (\Input::method() == 'POST')
		{
			$val = $form->validation();
			if ($val->run())
			{
				//\Util_security::check_csrf();

				$note = Model_Note::forge(array(
					'title' => \Input::post('title'),
					'body' => \Input::post('body'),
					'member_id' => $this->current_user->id,
				));

				if ($note and $note->save())
				{
					\Session::set_flash('message', \Config::get('site.term.note').'日記を作成しました。');
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

	public function form()
	{
		$form = \Fieldset::forge();

		$form->add('title', 'タイトル', array('size' => 66))
			->add_rule('trim')
			->add_rule('required')
			->add_rule('max_length', 255);

		$form->add('body', '本文', array('type' => 'textarea', 'cols' => 81, 'rows' => 10))
			->add_rule('required');

		$form->add('submit', '', array('type'=>'submit', 'value' => '送信'));
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Security::fetch_token()));

		return $form;
	}
}
