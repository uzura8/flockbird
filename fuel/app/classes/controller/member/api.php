<?php

class Controller_Member_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Api list
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_list()
	{
		$this->api_accept_formats = array('json', 'html');
		$this->controller_common_api(function() {
			$default_params = array(
				'latest' => 1,
				'desc' => 1,
				'limit' => conf('member.view_params.list.limit'),
			);
			list($limit, $is_latest, $is_desc, $since_id, $max_id)
				= $this->common_get_list_params($default_params, conf('member.view_params.list.limit_max'));
			list($wheres, $search_word_str) = Site_Model::get_search_word_conds(\Input::get('q'), 'name', false, false, true);
			list($list, $next_id) = Model_Member::get_list($wheres, $limit, $is_latest, $is_desc, $since_id, $max_id);
			$this->set_response_body_api(array(
				'list' => $list,
				'next_id' => $next_id,
				'since_id' => $since_id,
				'get_uri' => 'member/api/list.json',
				'history_keys' => array('q', 'max_id'),
				'get_data_list' => array('q' => $search_word_str),
			), '_parts/member_list');
		});
	}

	/**
	 * Api search
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_search()
	{
		$this->api_accept_formats = array('json', 'html');
		$this->controller_common_api(function()
		{
			if (!conf('profile.useCacheTable.isEnabled', 'member')) throw new HttpNotFoundException;

			$default_params = array(
				'page' => 1,
				'sort' => 'id',
				'desc' => 1,
				'limit' => conf('member.view_params.list.limit'),
			);
			list($limit, $page, $loaded_position) = $this->common_get_pager_list_params(
				conf('member.view_params.list.limit'),
				conf('member.view_params.list.limit_max')
			);
			$data = array(
				'limit' => $limit,
				'no_data_message' => sprintf('指定の%sに該当する%sがいません。', term('common.condition'), term('member.view')),
				'loaded_position' => $loaded_position,
			);
			$data = array_merge($data, Site_Member::get_detail_search_pager_list(get_uid(), $limit, $page));
			$this->set_response_body_api($data, '_parts/member_search_list');
		});
	}
}
