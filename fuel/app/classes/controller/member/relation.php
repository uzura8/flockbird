<?php

class Controller_Member_Relation extends Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber setting
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list($type = null)
	{
		if (!in_array($type, array('follow', 'access_block'))) throw new HttpNotFoundException;

		$relation_type_camelized_lower = Inflector::camelize($type, true);
		$this->set_title_and_breadcrumbs(term($relation_type_camelized_lower.'ed', 'member.view', 'site.list'), array(
			'member/setting' => term('site.setting', 'site.item', 'site.list'),
		), $this->u);

		$default_params = array(
			'latest' => 1,
			'desc' => 1,
			'limit' => conf('member.view_params.list.limit'),
		);
		list($limit, $is_latest, $is_desc, $since_id, $max_id)
			= $this->common_get_list_params($default_params, conf('member.view_params.list.limit_max'));
		list($list, $next_id) = Model_MemberRelation::get_list(array(
			'member_id_from' => $this->u->id,
			'is_'.$type => 1,
		), $limit, $is_latest, $is_desc, $since_id, $max_id, 'member');

		$this->template->main_container_attrs = array('data-not_render_site_summary' => 1);
		$this->template->content = \View::forge('member/relation/list', array(
			'list' => $list,
			'next_id' => $next_id,
			'since_id' => $since_id,
			'max_id' => $max_id,
		));
		$this->template->post_footer = \View::forge('_parts/load_item');
	}
}
