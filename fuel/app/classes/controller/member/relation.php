<?php

class Controller_Member_Relation extends Controller_Base_Site
{
	protected $check_not_auth_action = array(
		'list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Index
	 * 
	 * @access  public
	 * @param   string $typeember_id
	 * @param   int    $member_id
	 * @return  Response
	 */
	public function action_list($type, $member_id = null)
	{
		$type = Inflector::singularize($type);
		if (!Site_Member_Relation::check_enabled_relation_type($type)) throw new HttpNotFoundException;
		$relation_type = $type == 'follower' ? 'follow' : $type;

		if (!$member_id && Auth::check())
		{
			$member = $this->u;
		}
		else
		{
			$member = Model_Member::check_authority($member_id);
		}
		if ($type == 'access_block' && $member->id != get_uid()) throw new HttpNotFoundException;

		$relation_type_camelized_lower = Inflector::camelize($type, true);
		$this->set_title_and_breadcrumbs(term($relation_type_camelized_lower, 'site.list_kana'), null, $member);

		$default_params = array(
			'latest' => 1,
			'desc' => 1,
			'limit' => conf('member.view_params.list.limit'),
		);
		list($limit, $is_latest, $is_desc, $since_id, $max_id)
			= $this->common_get_list_params($default_params, conf('member.view_params.list.limit_max'));

		$member_id_prop = $type == 'follower' ? 'member_id_to' : 'member_id_from';
		list($list, $next_id) = Model_MemberRelation::get_list(array(
			$member_id_prop => $member->id,
			'is_'.$relation_type => 1,
		), $limit, $is_latest, $is_desc, $since_id, $max_id, $type == 'follower' ? 'member_from' : 'member');

		$this->template->main_container_attrs = array('data-not_render_site_summary' => 1);
		$this->template->content = \View::forge('member/relation/list', array(
			'list' => $list,
			'next_id' => $next_id,
			'since_id' => $since_id,
			'max_id' => $max_id,
			'type' => $type,
			'member' => $member,
		));
		$this->template->post_footer = \View::forge('_parts/load_item');
	}
}
