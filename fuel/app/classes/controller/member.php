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
		$default_params = array(
			'desc' => 1,
			'latest' => 1,
			'limit' => conf('timeline.articles.limit'),
		);
		list($limit, $is_latest, $is_desc, $since_id, $max_id)
			= $this->common_get_list_params($default_params, conf('timeline.articles.max_limit'));
		list($list, $next_id)
			= \Timeline\Site_Model::get_list(\Auth::check() ? $this->u->id : 0, $member->id, false, null, $max_id, $limit, $is_latest, $is_desc, $since_id);
		$liked_timeline_ids
			= \Auth::check() ? \Timeline\Model_TimelineLike::get_timeline_ids4member_id_and_timeline_ids($this->u->id, \Util_Orm::conv_col2array($list, 'timeline_id')) : array();

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
			'next_id' => $next_id,
			'since_id' => $since_id ?: 0,
			'is_display_load_before_link' => $max_id ? true : false,
			'liked_timeline_ids' => $liked_timeline_ids,
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
		$this->set_title_and_breadcrumbs(term('member.view', 'site.list'));

		$default_params = array(
			'latest' => 1,
			'desc' => 1,
			'limit' => conf('member.view_params.list.limit'),
		);
		list($limit, $is_latest, $is_desc, $since_id, $max_id)
			= $this->common_get_list_params($default_params, conf('member.view_params.list.limit_max'));
		list($list, $next_id) = Model_Member::get_list(null, $limit, $is_latest, $is_desc, $since_id, $max_id);

		$this->template->content = \View::forge('_parts/member_list', array(
			'list' => $list,
			'next_id' => $next_id,
			'since_id' => $since_id,
			'get_uri' => 'member/api/list.html',
			'history_key' => 'max_id',
			'is_display_load_before_link' => $max_id ? true : false,
		));
		$this->template->post_footer = \View::forge('_parts/load_item');
	}
}
