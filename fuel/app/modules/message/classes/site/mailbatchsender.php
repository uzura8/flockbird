<?php
namespace Message;

class Site_MailBatchSender extends \Site_MailBatchSender
{
	public function __construct($options = array(), $mail_handler = null)
	{
		parent::__construct($options, $mail_handler);
	}

	protected function get_queues()
	{
		$queues = \Message\Model_MessageRecievedMailQueue::get_all(
			array('id' => 'ASC',),
			array('message_recieved', 'message_recieved.message', 'message_recieved.message.member', 'member'),
			$this->options['queues_limit'],
			array('status' => $this->get_status_value('unexecuted')),
			array('message_recieved_id' => true, 'member_id' => true, 'status' => true),
			$this->max_count ? false : true
		);
		if ($this->max_count) return $queues;

		$this->set_max_count($queues[1]);

		return $queues[0];
	}

	protected function set_mail_data()
	{
		list($is_send, $member, $message_recieved) = $this->check_valid_queue();
		if (!$is_send) return;

		$this->mail_data['to_name']  = $member->name;
		$this->mail_data['to_email'] = $member->member_auth->email;
		$this->mail_data['content']  = $this->get_mail_body($message_recieved, $member->id);
	}

	protected function get_mail_body(Model_MessageRecieved $message_recieved, $target_member_id)
	{
		$data = $message_recieved->to_array();
		$data['received_at'] = $message_recieved->created_at;
		$data['message_id'] = $message_recieved->message_id;
		$data['message_type_name'] = Site_Util::get_type_label($message_recieved->message->type);
		$data['message_subject'] = $message_recieved->message->subject ?: '';
		$data['message_body'] = $message_recieved->message->body;
		$data['member_name_from'] = member_name($message_recieved->message->member);

		return render('message::mail/_parts/notice', $data);
	}

	protected function check_valid_queue()
	{
		$is_send = false;
		$member = null;
		$message_recieved = null;
		$error_message_prefix = 'Invalid data: ';

		if (!$member = $this->get_member())
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'failed to get target member';
			return array($is_send, $member, $message_recieved);
		}
		if (empty($member->member_auth->email))
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'member not set email';
			return array($is_send, $member, $message_recieved);
		}
		if (!static::check_is_set_to_send_mail($member->id))
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'member not set to send notice_mail';
			return array($is_send, $member, $message_recieved);
		}
		if (!$message_recieved = !empty($this->each_queue->message_recieved) ? $this->each_queue->message_recieved
			: Model_MessageRecieved::get_one4id($this->each_queue->message_recieved_id, 'message'))
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'failed to get related message_recieved';
			return array($is_send, $member, $message_recieved);
		}
		if ($message_recieved->is_read)
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = 'Already read';
			return array($is_send, $member, $message_recieved);
		}
		if (empty($message_recieved->message)) $message_recieved->message = Model_Message::get_one4id($message_recieved->message_id);
		if (empty($message_recieved->message))
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'failed to get related message';
			return array($is_send, $member, $message_recieved);
		}
		if (empty($message_recieved->message->member))
		{
			$message_recieved->message->member = \Model_Member::get_one4id($message_recieved->message->member_id);
		}

		return array(true, $member, $message_recieved);
	}

	protected function get_member()
	{
		$member = !empty($this->each_queue->member) ?
			$this->each_queue->member : \Model_Member::get_one4id($this->each_queue->member_id, 'member_auth');
		if (empty($member->member_auth))
		{
			$member->member_auth = \Model_MemberAuth::get_one4unique_key($this->each_queue->member_id, array(), 'member_id');
		}

		return $member;
	}

	protected function check_is_set_to_send_mail($member_id)
	{
		return (bool)\Model_MemberConfig::get_value($member_id, 'notice_messageMailMode', true);
	}
}

