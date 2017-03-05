<?php
namespace Message;

class Controller_Message extends \Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Message index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Message list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$data = array();
		$this->set_title_and_breadcrumbs(term('message.list'), null, $this->u);
		$this->template->subtitle = \View::forge('notice::_parts/link_read_all', array(
			'tag_attr' => array('class' => 'pull-right'),
			'is_message' => true,
		));
		$this->template->content = \View::forge('_parts/list_block', $data);
	}

	/**
	 * Message member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		if (!$member_id = intval($member_id)) throw new \HttpNotFoundException;
		list($is_mypage, $member, $access_from) = $this->check_auth_and_is_mypage($member_id);
		if ($is_mypage) throw new \HttpNotFoundException;

		// Change status to read for notice
		$this->change_message_status2read('member', $member_id);

		// Link to report
		$this->template->subtitle = \View::forge('_parts/member_subtitle', array(
			'report_data' => $this->set_global_for_report_form() ? array(
				'member_id' => $member_id,
				'uri' => 'message/member/'.$member_id,
				'type' => 'message_member',
				'content' => '',
			) : array()
		));

		$this->template->post_header = \View::forge('_parts/member_header');
		$this->template->post_footer = \View::forge('_parts/load_message');
		$this->set_title_and_breadcrumbs(__('message_title_message_with', array('name' => $member->name)), array(
			'message' => term('message.list')
		), $this->u);
		$this->template->content = \View::forge('member', array('type' => 'member', 'related_id' => $member_id));
	}

	/**
	 * Message detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		$message_id = (int)$id;
		$message = Model_Message::check_authority($message_id, null, 'member');
		if (!$target_member_ids = Site_Model::get_send_target_member_ids($message->id, $message->type, null, $message->member_id))
		{
			throw new \HttpNotFoundException;
		}
		if (!in_array($this->u->id, $target_member_ids)) throw new \HttpForbiddenException;

		// Update read flag
		$this->change_message_status2read($message->type, $message->member_id, $message->id);
		Model_MessageRecieved::update_is_read4member_ids_and_message_ids($this->u->id, $message->id);

		$title = array('name' => $message->subject);
		$header_info = array();
		if (!$message->is_sent)
		{
			$title['label'] = array('name' => term('form.draft'));
			$header_info = array('body' => __('message_not_send_for', array('label' => t('message.view'))));
		}
		elseif (Site_Util::check_admin_type($message->type))
		{
			$title['subtitle'] = Site_Util::get_type_label($message->type);
		}
		$this->set_title_and_breadcrumbs($title, array('message' => term('message.view')), $this->u, null, $header_info);
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('message' => $message));
		//$this->template->post_footer = \View::forge('_parts/detail_footer', array('is_mypage' => check_uid($note->member_id)));
		$this->template->content = \View::forge('detail', array('message' => $message));
	}
}
