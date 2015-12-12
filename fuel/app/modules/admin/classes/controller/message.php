<?php
namespace Admin;

class Controller_Message extends Controller_Admin
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * The index action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{		
		$is_draft = \Input::get('is_draft', 0);
		$is_sent = \Util_toolkit::reverse_bool($is_draft, true);

		$data = array();
		$data['is_draft'] = $is_draft;
		list($data['list'], $data['pagination']) = \Site_Model::get_pagenation_list('message', array(
			'where' => array('type', 'in', \Message\Site_Util::get_types(true, true)),
		));
		$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs(term('message.view', 'site.management'));
		$this->template->subtitle = \View::forge('message/_parts/list_subtitle');
		$this->template->content = \View::forge('message/list', $data);
	}

	/**
	 * The list action.
	 * 
	 * @access  public
	 * @return  void
	 */
	Public function action_list()
	{	
		$this->action_index();
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
		$message = \Message\Model_Message::check_authority($id);
		$target_member_ids = \Message\Site_Util::check_type($message->type, 'site_info_all') ?
			array() : \Message\Site_Model::get_send_target_member_ids($message->id, $message->type, null, $message->member_id);
		$target_members = $target_member_ids ? \Model_Member::get_basic4ids($target_member_ids, null, true) : array();

		$title = array('name' => $message->subject);
		$header_info = array();
		if (!$message->is_sent)
		{
			$header_info = array('body' => sprintf('この%sはまだ%sされていません。', term('message.view'), term('form.send')));
			$this->template->subtitle = \View::forge('message/_parts/detail_subtitle', array('message' => $message));
		}
		elseif (\Message\Site_Util::check_admin_type($message->type))
		{
			$title['subtitle'] = \Message\Site_Util::get_type_label($message->type);
		}
		$this->set_title_and_breadcrumbs($title, array('admin/message' => term('message.view', 'admin.view')), null, null, $header_info);
		$this->template->subtitle = \View::forge('message/_parts/detail_subtitle', array('message' => $message));
		$this->template->content = \View::forge('message/detail', array(
			'message' => $message,
			'type' => $message->type,
			'target_members' => $target_members,
		));
	}

	/**
	 * Message create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create($target_type, $target_id = null)
	{
		if (!\Message\Site_Util::check_type($target_type, array('site_info_all', 'member'))) throw new \HttpNotFoundException;
		$member = ($target_type == 'member') ? \Model_Member::check_authority($target_id) : null;

		$message = \Message\Model_Message::forge();
		$val = self::get_validation_object($message);

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			$error_message = '';
			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				if (!strlen($post['body'])) throw new \ValidationFailedException(term('message.form.body').'が入力されていません。');

				$member_id_from = conf('adminMail.memberIdFrom', 'message');
				$type_key = $target_type == 'member' ? 'site_info' : $target_type;
				$type = \Message\Site_Util::get_type4key($type_key);
				\DB::start_transaction();
				$message->save_with_relations($member_id_from, $type, $target_id, $post['body'], $post['subject'], $post['is_draft'], array(
					'admin_user_id' => $this->u->id,
				));
				\DB::commit_transaction();

				$message_success = sprintf('%sを%sしました。', term('message.view'), $message->is_sent ? term('form.send') : term('form.draft'));
				\Session::set_flash('message', $message_success);
				\Response::redirect('admin/message/detail/'.$message->id);
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(term('message.view', 'form.create'), array('admin/message' => term('message.view', 'admin.view')));
		$this->template->content = \View::forge('message/_parts/form', array(
			'val' => $val,
			'target_type' => $target_type,
			'member' => $member,
		));
	}

	/**
	 * Message edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		$message = \Message\Model_Message::check_authority($id);
		$type_key = \Message\Site_Util::get_key4type($message->type);
		if (!\Message\Site_Util::check_type($type_key, array('site_info', 'site_info_all'))) throw new \HttpNotFoundException;

		$message_sent_admins = \Message\Model_MessageSentAdmin::get4message_id($id, array('member'));
		if (\Message\Site_Util::check_type($type_key, 'site_info') && !$message_sent_admins)
		{
			throw new \HttpNotFoundException;
		}

		$val = self::get_validation_object($message);
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			$error_message = '';
			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				$message->set_values($post);

				$success_message = sprintf('%sを%sしました。', term('message.view'), term('form.edit'));
				if ($is_send = (!$message->is_sent && empty($post['is_draft'])))
				{
					$success_message = sprintf('%sを%sしました。', term('message.view'), term('form.send'));
				}
				\DB::start_transaction();
				$message->update_messaage($is_send, null, $post);
				\DB::commit_transaction();

				\Session::set_flash('message', $success_message);
				\Response::redirect('admin/message/'.$message->id);
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $error_message);
			}
		}

		$message_sent_admin = $message_sent_admins ? array_shift($message_sent_admins) : null;
		$this->set_title_and_breadcrumbs(term('form.edit'), array(
			'admin/message' => term('message.view','admin.view'),
			'admin/message/'.$message->id => $message->subject
		));
		$this->template->content = \View::forge('message/_parts/form', array(
			'is_edit' => true,
			'val' => $val,
			'message' => $message,
			'target_type' => $type_key == 'site_info_all' ? 'all' : 'member',
			'member' => $message_sent_admin ? $message_sent_admin->member : null,
		));
	}

	/**
	 * Message delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		$message = \Message\Model_Message::check_authority($id);
		if ($message->is_sent) new \HttpForbiddenException;

		try
		{
			\DB::start_transaction();
			$message->delete();
			\DB::commit_transaction();
			\Session::set_flash('message', term('message.view').'を削除しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect(\Site_Util::get_redirect_uri('admin/message'));
	}

	/**
	 * Message send
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_send($id = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		$message = \Message\Model_Message::check_authority($id);

		$redirect_uri = \Site_Util::get_redirect_uri('admin/message/'.$id);

		if ($message->is_sent)
		{
			\Session::set_flash('error', sprintf('既に%sされています。', term('form.send')));
			\Response::redirect($redirect_uri);
		}

		try
		{
			\DB::start_transaction();
			$message->update_messaage(true);
			\DB::commit_transaction();
			\Session::set_flash('message', sprintf('%sを%sしました。', term('message.view'), term('form.send')));
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect($redirect_uri);
	}


	private static function get_validation_object(\Message\Model_Message $message)
	{
		$val = \Validation::forge();
		$val->add_model($message);

		$val->fieldset()->field('subject')->add_rule('required');
		$val->fieldset()->field('body')->add_rule('required');

		if (empty($message->is_sent))
		{
			$val->add('is_draft', term('form.draft'))
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', array(0, 1));
		}

		return $val;
	}
}

/* End of message.php */
