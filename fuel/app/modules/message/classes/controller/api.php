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
			list($type_key, $type, $related_id, $members, $group) = $this->validate_talks_params($type_key, $id);

			$data = Site_Util::get_talks4view(
				get_uid(),
				$type_key,
				$related_id,
				$this->common_get_list_params(array(
					'desc'   => 0,
					'latest' => 1,
					'limit'  => conf('articles.limit', 'message'),
				), conf('articles.limit_max', 'message'), true)
			);
			if ($group) $data['group'] = $group;
			//$data['is_display_load_before_link'] = (bool)\Input::get('before_link', false);
			$data['get_uri'] = sprintf('message/api/talks/%s/%d.html', $type_key, $id);

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

			$message->send_message($this->u->id, $type, $related_id, $post['body']);

			$data = array(
				'id'      => $message->id,
				'message' => term('message.view').'を'.term('form.post').'しました。',
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
		$members = array();
		switch ($type_key)
		{
			case 'member':
				if ($request_id == $this->u->id) throw new \HttpNotFoundException;
				\Model_Member::check_authority($request_id);
				$related_id = \Model_MemberRelationUnit::get_id4member_ids(array($request_id, $this->u->id));
				break;
			case 'group':
				$group = \Group\Model_Group::check_authority($related_id);
				$members = \Group\Model_GroupMember::get4id($related_id);
				if (!\Util_Orm::check_included($related_id, 'member_id', $group_members)) throw new HttpForbiddenException;
				break;
		}

		return array($type_key, $type, $related_id, $members, $group);
	}

	/**
	 * Get edit menu
	 * 
	 * @access  public
	 * @param   int  $id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_get_menu_common
	 */
	//public function get_menu($id = null)
	//{
	//	$this->api_get_menu_common('message', $id, true, 'messageBox_');
	//}

	/**
	 * Delete message
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	//public function post_delete($id = null)
	//{
	//	$this->api_delete_common('message', $id);
	//}
}

