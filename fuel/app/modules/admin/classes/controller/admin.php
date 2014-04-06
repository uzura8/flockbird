<?php
namespace Admin;

class Controller_Admin extends Controller_Base {

	//public $template = 'admin/template';
	protected $check_not_auth_action = array(
		'login',
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
		$this->set_current_user();

		if (!IS_API)
		{
			$this->template->layout = 'normal';
		}
	}
	
	public function action_login()
	{
		// Already logged in
		\Auth::check() and \Response::redirect('admin');

		$destination = \Session::get_flash('destination') ?: \Input::post('destination', '');
		$val = \Validation::forge();
		$val->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Util_security::get_csrf()));
		$val->add('email', 'Username')->add_rule('required');
		$val->add('password', 'Password', array('type' => 'password'))->add_rule('required');

		if (\Input::method() == 'POST')
		{
			if ($val->run())
			{
				$auth = \Auth::instance();
				
				// check the credentials. This assumes that you have the previous table created
				if (\Auth::check() or $auth->login(\Input::post('email'), \Input::post('password')))
				{
					// credentials ok, go right in
					\Session::set_flash('message', 'Welcome, '.$auth->get_screen_name());
					\Response::redirect('admin');
				}
				else
				{
					\Session::set_flash('error', 'Fail');
				}
			}
		}

		$this->set_title_and_breadcrumbs('Login', null, null, null, null, true);
		$this->template->content = \View::forge('login', array('val' => $val, 'destination' => $destination));
	}
	
	/**
	 * The logout action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_logout()
	{		
		\Auth::logout();
		\Response::redirect('admin');
	}

	/**
	 * The index action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{		
		$this->set_title_and_breadcrumbs(term(array('admin.view', 'page.view')).' Dashboard', null, null, null, null, true);
		$this->template->content = \View::forge('dashboard');
	}
}

/* End of file admin.php */
