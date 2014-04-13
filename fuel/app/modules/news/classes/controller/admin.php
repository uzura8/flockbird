<?php
namespace News;

class Controller_Admin extends \Admin\Controller_Admin
{
	protected $is_admin = true;
	protected $auth_driver = 'SimpleAuth';
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * The index action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{		
		$this->set_title_and_breadcrumbs(term(array('news.view', 'site.management')));
		//$this->template->layout = 'wide';
		$this->template->content = \View::forge('admin/list', array());
	}

	/**
	 * The list action.
	 * 
	 * @access  public
	 * @return  void
	 */
	Public function action_list()
	{	
		$this->action_index();
	}
}

/* End of admin.php */
