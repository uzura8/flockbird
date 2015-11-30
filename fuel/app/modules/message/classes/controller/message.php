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

		$this->template->post_footer = \View::forge('_parts/load_message');
		$this->set_title_and_breadcrumbs(sprintf('%s との%s', $member->name, term('message.view')), array('message' => term('message.view')));
		$this->template->content = \View::forge('member', array('type' => 'member', 'related_id' => $member_id));
	}
}
