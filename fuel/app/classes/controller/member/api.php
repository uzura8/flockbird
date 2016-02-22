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
				'history_key' => 'max_id',
				'get_data_list' => array('q' => $search_word_str),
			), '_parts/member_list');
		});
	}
}
