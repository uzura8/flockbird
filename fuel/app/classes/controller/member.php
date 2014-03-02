<?php

class Controller_Member extends Controller_Site
{
	protected $check_not_auth_action = array(
		'home',
		'resend_password',
		'confirm_reset_password',
		'reset_password',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
	}

	/**
	 * Mmeber profile
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_home($id = null)
	{
		$id = (int)$id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($id);
		list($list, $is_next) = \Timeline\Site_Model::get_list(Auth::check() ? $this->u->id : 0, $id, $is_mypage);

		$this->set_title_and_breadcrumbs($member->name.' さんのページ');
		$this->template->subtitle = View::forge('_parts/home_subtitle', array('member' => $member));
		$this->template->post_footer = \View::forge('timeline::_parts/load_timelines');
		$this->template->content = \View::forge('member/home', array('member' => $member, 'list' => $list, 'is_next' => $is_next));
	}
}
