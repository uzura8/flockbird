<?php

/**
 * The Site Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Site extends Controller_Base
{
	public function before()
	{
		parent::before();

		$this->auth_check('site/login');
		$this->set_current_user();

		$this->template->header_keywords = '';
		$this->template->header_description = '';
	}

	private function set_current_user()
	{
		$auth = Auth::instance();
		$this->u = Auth::check() ? Model_Member::find()->where('id', $auth->get_member_id())->related('memberauth')->get_one() : null;

		View::set_global('u', $this->u);
	}

	protected function add_member_filesize_total($size)
	{
		if (!$this->u) throw new Exception('Not authenticated.');

		$this->u->filesize_total += $size;
		$this->u->save();

		return $this->u->filesize_total;
	}

	/**
	 * Site index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = PRJ_SITE_NAME.'メインメニュー';
		$this->template->header_title = site_title();
		//$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '');

		$this->template->content = View::forge('site/index');
	}

	public function action_login()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		$val = Validation::forge();

		if (Input::method() == 'POST')
		{
			$val->add('email', 'メールアドレス')->add_rule('required');
			$val->add('password', 'パスワード')->add_rule('required');
			$val->add('destination');

			if ($val->run())
			{
				$auth = Auth::instance();

				// check the credentials. This assumes that you have the previous table created
				if (Auth::check() or $auth->login(Input::post('email'), Input::post('password')))
				{
					// credentials ok, go right in
					Session::set_flash('message', 'ログインしました');

					if (Input::post('destination')) Response::redirect(urldecode(Input::post('destination')));
					Response::redirect('member');
				}
				else
				{
					Session::set_flash('error', 'ログインに失敗しました');
				}
			}
		}

		$title = 'ログイン';
		$this->template->title = $title;
		$this->template->header_title = site_title($title);
		$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '/', $title => '');

		$destination = '';
		$destination = (Input::post('destination')) ? Input::post('destination') : null;
		$destination = (Session::get_flash('destination')) ? Session::get_flash('destination') : null;
		$this->template->content = View::forge('site/login', array('val' => $val, 'destination' => $destination));
	}

	/**
	 * The logout action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_logout()
	{
		if ($this->u->register_type == 1) Response::redirect('facebook/logout');

		Auth::logout();
		Session::set_flash('message', 'ログアウトしました');
		Response::redirect('site/login');
	}
}
