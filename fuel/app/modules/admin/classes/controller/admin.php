<?php
namespace Admin;

class Controller_Admin extends \Controller_Base {

	//public $template = 'admin/template';
	protected $check_not_auth_action = array(
		'login',
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
		$this->set_current_user();

		$this->template->header_keywords = '';
		$this->template->header_description = '';
	}

	protected function auth_check()
	{
		if (!$this->check_not_auth_action() && !\Auth::check())
		{
			\Session::set_flash('destination', urlencode(\Input::server('REQUEST_URI')));
			\Response::redirect('admin/login');
		}

	}

	private function set_current_user()
	{
		$this->u = null;
		if (\Auth::check())
		{
			$auth = \Auth::instance();
			$user_id = $auth->get_user_id();
			$this->u = Model_User::find()->where('id', $user_id[1])->get_one();
		}
		\View::set_global('u', $this->u);
	}
	
	public function action_login()
	{
		// Already logged in
		\Auth::check() and \Response::redirect('admin');

		$val = \Validation::forge();

		if (\Input::method() == 'POST')
		{
			$val->add('email', 'Email or Username')
			    ->add_rule('required');
			$val->add('password', 'Password')
			    ->add_rule('required');

			if ($val->run())
			{
				$auth = \Auth::instance();
				
				// check the credentials. This assumes that you have the previous table created
				if (\Auth::check() or $auth->login(\Input::post('email'), \Input::post('password')))
				{
					// credentials ok, go right in
					\Session::set_flash('notice', 'Welcome, '.$this->u->username);
					\Response::redirect('admin');
				}
				else
				{
					$this->template->set_global('login_error', 'Fail');
				}
			}
		}

		$this->template->title = 'Login';
		$this->template->content = \View::forge('admin/login', array('val' => $val));
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
		$this->template->title = 'Dashboard';
		$this->template->content = \View::forge('admin/dashboard');
	}
}

/* End of file admin.php */
