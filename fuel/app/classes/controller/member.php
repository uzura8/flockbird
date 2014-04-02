<?php

class Controller_Member extends Controller_Site
{
	protected $check_not_auth_action = array(
		'home',
		'list',
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
	 * Mmeber home
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_home($id = null)
	{
		$id = (int)$id;
		list($is_mypage, $member, $access_from) = $this->check_auth_and_is_mypage($id);
		list($list, $is_next) = \Timeline\Site_Model::get_list(Auth::check() ? $this->u->id : 0, $id);
		$member_profiles = Model_MemberProfile::get4member_id($member->id, true);

		$this->set_title_and_breadcrumbs($member->name.' さんのページ');
		$this->template->post_footer = \View::forge('timeline::_parts/load_timelines');
		$this->template->content = \View::forge('member/home', array(
			'member' => $member,
			'member_profiles' => $member_profiles,
			'is_mypage' => $is_mypage,
			'access_from' => $access_from,
			'display_type' => 'summery',
			'list' => $list,
			'is_next' => $is_next,
		));
	}

	/**
	 * Mmeber list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$this->set_title_and_breadcrumbs(term(array('member.view', 'site.list')));
		$sort = conf('member.view_params.list.sort');
		$data = Site_Model::get_simple_pager_list('member', 1, array(
			'order_by' => array($sort['property'] => $sort['direction']),
			'limit'    => conf('member.view_params.list.limit'),
		));
		$this->template->content = \View::forge('member/_parts/list', $data);
		$this->template->post_footer = \View::forge('_parts/load_item');
	}
}
