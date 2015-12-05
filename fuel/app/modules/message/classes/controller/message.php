<?php
namespace Message;

class Controller_Message extends \Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Message index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Message list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$data = array();
		$this->set_title_and_breadcrumbs(term('message.view'), null, $this->u);
		$this->template->content = \View::forge('_parts/list_block', $data);
	}

	/**
	 * Message member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		if (!$member_id = intval($member_id)) throw new \HttpNotFoundException;
		list($is_mypage, $member, $access_from) = $this->check_auth_and_is_mypage($member_id);
		if ($is_mypage) throw new \HttpNotFoundException;

		// 既読処理
		$this->change_message_status2read($member_id);

		$this->template->post_footer = \View::forge('_parts/load_message');
		$this->set_title_and_breadcrumbs(sprintf('%s との%s', $member->name, term('message.view')), array('message' => term('message.view')));
		$this->template->content = \View::forge('member', array('type' => 'member', 'related_id' => $member_id));
	}
}
