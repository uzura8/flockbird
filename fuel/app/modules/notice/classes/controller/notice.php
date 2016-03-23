<?php
namespace Notice;

class Controller_Notice extends \Controller_Site
{
	protected $check_not_auth_action = array();

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
		$this->template->subtitle = \View::forge('_parts/link_read_all', array(
			'tag_attr' => array('class' => 'pull-right'),
		));
		$this->template->content = \View::forge('_parts/list_block', $data);
	}
}
