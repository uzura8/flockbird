<?php

class Controller_Member_Profile extends Controller_Member
{
	protected $check_not_auth_action = array(
		'index',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber_profile index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index($id = null)
	{
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($id);
		$this->set_title_and_breadcrumbs(sprintf('%sの%s', $is_mypage ? '自分' : $member->name.'さん', term('profile')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('member/profile/_parts/profile_subtitle') : '';
		$this->template->content = View::forge('member/profile/index', array('member' => $member, 'is_mypage' => $is_mypage));
	}
}
