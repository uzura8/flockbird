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
	 * Mmeber mypage
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_mypage()
	{
		$this->action_home($this->u->id);
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
		$member_profiles = Model_MemberProfile::get4member_id($member->id, true);
		$data = array(
			'member' => $member,
			'member_profiles' => $member_profiles,
			'is_mypage' => $is_mypage,
			'access_from' => $access_from,
			'display_type' => 'summary',
		);
		if (is_enabled('timeline'))
		{
			$data['timeline'] = \Timeline\Site_Util::get_list4view(
				\Auth::check() ? $this->u->id : 0,
				$member->id, false, null,
				$this->common_get_list_params(array(
					'desc' => 1,
					'latest' => 1,
					'limit' => conf('articles.limit', 'timeline'),
				), conf('articles.limit_max', 'timeline'), true)
			);
			$data['timeline']['member'] = $member;
			$this->template->post_footer = \View::forge('timeline::_parts/load_timelines');
		}
		$this->set_title_and_breadcrumbs($member->name.' さんのページ',
			array('member/list' => term('member.view', 'site.list')),
			null, null, array(), false, false, array(
				'title' => $member->name.' さんのページ',
				'image' => Site_Util::get_image_uri4file_name($member->get_image(), 'P_L', 'profile'),
			)
		);
		$this->template->content = \View::forge('member/home', $data);
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
		list($wheres, $search_word_str) = Site_Model::get_search_word_conds(\Input::get('q'), 'name', false, false, true);
		list($list, $next_id) = Model_Member::get_list($wheres, $limit, $is_latest, $is_desc, $since_id, $max_id);

		$this->template->main_container_attrs = array('data-not_render_site_summary' => 1);
		$this->template->content = \View::forge('member/list', array(
			'list' => $list,
			'next_id' => $next_id,
			'since_id' => $since_id,
			'max_id' => $max_id,
			'search_word' => $search_word_str,
		));
		$this->template->post_footer = \View::forge('_parts/load_item');
	}
}
