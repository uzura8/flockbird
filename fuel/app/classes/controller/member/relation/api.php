<?php

class Controller_Member_Relation_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get member_relation list by type and member
	 * 
	 * @access  public
	 * @param   string $typeember_id
	 * @param   int    $member_id
	 * @return  Response (json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list($type = null, $member_id = null)
	{
		$this->api_accept_formats = array('json', 'html');
		$this->controller_common_api(function() use ($type, $member_id)
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
			), $limit, $is_latest, $is_desc, $since_id, $max_id, 'member');

			$this->set_response_body_api(array(
				'is_simple_list' => true,
				'is_hide_fallow_btn' => $type == 'access_block',
				'is_display_access_block_btn' => $type == 'access_block',
				'list' => $list,
				'member_relation_name' => $type == 'follower' ? 'member_from' : 'member',
				'next_id' => $next_id,
				'since_id' => $since_id,
				'get_uri' => sprintf('member/relation/api/list/%s/%d.json', $type, $member->id),
				'history_keys' => array('q', 'max_id'),
			), '_parts/member_list');
		});
	}

	/**
	 * post_update
	 * 
	 * @access  public
	 * @return  Response (json)
	 */
	public function post_update($member_id_to = null, $relation_type = null)
	{
		$this->controller_common_api(function() use($member_id_to, $relation_type) {
			$this->response_body['errors']['message_default'] = sprintf('%sに%sしました。', term('form.update'), term('site.failure'));
			if (!Site_Member_Relation::check_enabled_relation_type($relation_type)) throw new HttpNotFoundException;

			if (!is_null(Input::post('id'))) $member_id_to = (int)Input::post('id');
			$member = Model_Member::check_authority($member_id_to);
			if ($member_id_to == $this->u->id) throw new HttpInvalidInputException;

			$member_relation = Model_MemberRelation::get4member_id_from_to($this->u->id, $member_id_to);
			if (!$member_relation) $member_relation = Model_MemberRelation::forge();
			$prop = 'is_'.$relation_type;
			$status_before = (bool)$member_relation->$prop;
			$status_after  = !$status_before;
			\DB::start_transaction();
			$member_relation->$prop = $status_after;
			$member_relation->member_id_to   = $member_id_to;
			$member_relation->member_id_from = $this->u->id;
			$member_relation->save();
			\DB::commit_transaction();
			$response_data = Site_Member_Relation::get_updated_status_info($relation_type, $status_after, true);
			$this->response_body = array_merge($this->response_body, $response_data);

			return $this->response_body;
		});
	}
}

