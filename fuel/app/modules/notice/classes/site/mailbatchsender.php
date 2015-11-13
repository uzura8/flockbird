<?php
namespace Notice;

class Site_MailBatchSender extends \Site_MailBatchSender
{
	protected function get_queues()
	{
		return \Notice\Model_NoticeMailQueue::get_all(
			array('id' => 'ASC',),
			array('member'),
			conf('noticeSendMail.limit.model.delete.withSendMail', 'task'),
			array('status' => $this->status_flags['unexecuted']),
			array('notice_status_id' => true, 'member_id' => true, 'status' => true)
		);
	}

	protected function set_mail_data()
	{
		list($is_send, $member, $notice_status) = $this->check_valid_queue();
		if (!$is_send) return;

		$this->mail_data['to_name']  = $member->name;
		$this->mail_data['to_email'] = $member->member_auth->email;
		$this->mail_data['content']  = $this->get_mail_body($notice_status, $member->id);
	}

	protected function get_mail_body(Model_NoticeStatus $notice_status, $target_member_id)
	{
		$data = $this->get_template_data_for_mail_body($notice_status, $target_member_id);

		return render('notice::mail/_parts/notice', $data);
	}

	protected function get_template_data_for_mail_body(Model_NoticeStatus $notice_status, $target_member_id)
	{
		$data = Site_Model::convert_notice_status_to_array_for_view($notice_status, $target_member_id);
		$data['foreign_table'] = $notice_status->notice->foreign_table;
		$data['type']          = $notice_status->notice->type;
		$data['foreign_id']    = $notice_status->notice->foreign_id;
		$data['parent_table']  = $notice_status->notice->parent_table;
		$data['parent_id']     = $notice_status->notice->parent_id;

		return $data;
	}

	protected function check_valid_queue()
	{
		$is_send = false;
		$member = null;
		$notice_status = null;
		$error_message_prefix = 'Invalid data: ';

		if (!$member = $this->get_member())
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'failed to get target member';
			return array($is_send, $member, $notice_status);
		}
		if (empty($member->member_auth->email))
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'member not set email';
			return array($is_send, $member, $notice_status);
		}
		if (!static::check_is_set_to_send_mail($member->id))
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'member not set to send notice_mail';
			return array($is_send, $member, $notice_status);
		}
		if (!$notice_status = Model_NoticeStatus::get_one4id($this->each_queue->notice_status_id, 'notice'))
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'failed to get related notice_status';
			return array($is_send, $member, $notice_status);
		}
		if ($notice_status->is_read)
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = 'Already read';
			return array($is_send, $member, $notice_status);
		}
		if (!$notice_status->notice)
		{
			$this->each_result = $this->get_status_value('skipped');
			$this->each_error_message = $error_message_prefix.'failed to get related notice';
			return array($is_send, $member, $notice_status);
		}

		return array(true, $member, $notice_status);
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
		return (bool)\Model_MemberConfig::get_value($member_id, 'notice_noticeMailMode', true);
	}
}

