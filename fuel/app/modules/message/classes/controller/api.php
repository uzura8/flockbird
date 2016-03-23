<?php
namespace Message;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Get message list
	 * 
	 * @access  public
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list()
	{
		$this->controller_common_api(function()
		{
			list($limit, $page) = $this->common_get_pager_list_params(view_params('limit', 'message'), view_params('limitMax', 'message'));
			$data = Model_MessageRecievedSummary::get_pager_list4member_id($this->u->id, $limit, $page);
			$list_array = array();
			foreach ($data['list'] as $key => $obj)
			{
				$list_array[] = Site_Model::convert_message_recieved_summary_to_array_for_view($obj, $this->u->id);
			}
			// json response
			$data['list'] = $list_array;
			$data['is_detail'] = (bool)\Input::get('is_detail', 0);
			$this->set_response_body_api($data);
		});
	}

	/**
	 * Get talk list
	 * 
	 * @access  public
	 * @param   string $type_key
	 * @param   int    $id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_talks($type_key = null, $id = null)
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function() use($type_key, $id)
		{
			list($type_key, $type, $related_id, $member_ids, $group) = $this->validate_talks_params($type_key, $id);

			$data = Site_Util::get_talks4view(
				$type_key,
				$related_id,
				$this->common_get_list_params(array(
					'desc'   => 0,
					'latest' => 1,
					'limit'  => view_params('limit', 'message'),
				), view_params('limitMax', 'message'), true),
				get_uid(),
				$member_ids
			);
			if ($group) $data['group'] = $group;
			//$data['is_display_load_before_link'] = (bool)\Input::get('before_link', false);
			$data['get_uri'] = sprintf('message/api/talks/%s/%d.html', $type_key, $id);

			if ($message_ids = \Util_Orm::conv_col2array($data['list'], 'message_id'))
			{
				Model_MessageRecieved::update_is_read4member_ids_and_message_ids($this->u->id, $message_ids);
			}

			$this->set_response_body_api($data, 'talks/_parts/list');
		});
	}

	/**
	 * Create message
	 * 
	 * @access  public
	 * @param   string $type_key
	 * @param   int    $id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_create($type_key = null, $id = null)
	{
		$this->controller_common_api(function() use($type_key, $id)
		{
			list($type_key, $type, $related_id, $group, $group_members) = $this->validate_talks_params($type_key, $id);
			$this->response_body['errors']['message_default'] = term('message.view').'の'.term('form.post').'に失敗しました。';
			$message = Model_Message::forge();
			$val = \Validation::forge();
			$val->add_model($message);
			if (!$val->run()) throw new \ValidationFailedException($val->show_errors());
			$post = $val->validated();
			if (!strlen($post['body'])) throw new \ValidationFailedException('Data is empty.');

			$message->save_with_relations($this->u->id, $type, $related_id, $post['body']);

			$data = array(
				'id'      => $message->id,
				'message' => term('message.view').'を'.term('form.post').'しました。',
			);
			$this->set_response_body_api($data);
		});
	}

	/**
	 * Update status already read all
	 * 
	 * @access  public
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_read_all()
	{
		$this->controller_common_api(function()
		{
			if (!is_enabled('notice')) throw new \HttpNotFoundException();

			\DB::start_transaction();
			$updated_count = \Message\Site_Util::change_all_status2read4member_id($this->u->id);
			\DB::commit_transaction();

			$data = array(
				'result' => (bool)$updated_count,
				'updated_count' => $updated_count,
			);
			$this->set_response_body_api($data);
		});
	}

	protected function validate_talks_params($type_key = null, $request_id = null)
	{
		if (!$request_id = intval($request_id ?: \Input::get('id'))) throw new HttpNotFoundException;
		if (!$type_key = $type_key ?: \Input::get('type')) throw new HttpNotFoundException;
		if (!in_array($type_key, array('group', 'member'))) throw new HttpNotFoundException;
		$type = Site_Util::get_type4key($type_key);

		$related_id = $request_id;
		$group = null;
		$member_ids = array();
		switch ($type_key)
		{
			case 'member':
				if ($request_id == $this->u->id) throw new \HttpNotFoundException;
				\Model_Member::check_authority($request_id);
				$related_id = \Model_MemberRelationUnit::get_id4member_ids(array($request_id, $this->u->id));
				$member_ids = array($request_id, $this->u->id);
				break;
			case 'group':
				$group = \Group\Model_Group::check_authority($related_id);
				if (!$member_ids = \Group\Model_GroupMember::get_member_ids4group_id($related_id)) throw new \HttpNotFoundException;
				if (!in_array($this->u->id, $member_ids)) throw new \HttpForbiddenException;
				break;
		}

		return array($type_key, $type, $related_id, $member_ids, $group);
	}
}

