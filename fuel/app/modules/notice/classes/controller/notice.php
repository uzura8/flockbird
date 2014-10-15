<?php
namespace Notice;

class Controller_Notice extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Notice index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Notice list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$data = array();
		$this->set_title_and_breadcrumbs(term('notice'), null, $this->u);
		$this->template->post_footer = \View::forge('_parts/list_footer', array('is_detail' => true));
		$this->template->content = \View::forge('_parts/list_block', $data);
	}
}
