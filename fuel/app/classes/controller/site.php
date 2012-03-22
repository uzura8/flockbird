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

		Config::load('site', 'site');
		$this->template->header_keywords = '';
		$this->template->header_description = '';
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
		$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '');

		$this->template->content = View::forge('site/index');
	}

	public function action_login()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		$val = Validation::forge();

		if (Input::method() == 'POST')
		{
			$val->add('email', 'Email or Username')
			    ->add_rule('required');
			$val->add('password', 'Password')
			    ->add_rule('required');

			if ($val->run())
			{
				$auth = Auth::instance();

				// check the credentials. This assumes that you have the previous table created
				if (Auth::check() or $auth->login(Input::post('email'), Input::post('password')))
				{
					// credentials ok, go right in
					Session::set_flash('message', 'ログインしました');
					Response::redirect('member');
				}
				else
				{
					$this->template->set_global('login_error', 'Fail');
				}
			}
		}

		$title = 'ログイン';
		$this->template->title = $title;
		$this->template->header_title = site_title($title);
		$this->template->breadcrumbs = array(Config::get('site.term.toppage') => '/', $title => '');

		$this->template->content = View::forge('site/login', array('val' => $val));
	}

	/**
	 * The logout action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_logout()
	{
		Auth::logout();
		Session::set_flash('message', 'ログアウトしました');
		Response::redirect('site/login');
	}
}
