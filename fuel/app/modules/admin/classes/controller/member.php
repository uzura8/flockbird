<?php
namespace Admin;

class Controller_Member extends Controller_Admin
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
		$data = array();
		list($data['list'], $data['pagination']) = \Site_Model::get_pagenation_list('member');

		$this->template->layout = 'wide';
		$this->set_title_and_breadcrumbs(term('member.view', 'site.management'));
		$this->template->subtitle = \View::forge('member/_parts/list_subtitle');
		$this->template->content = \View::forge('member/list', $data);
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
	 * The detail action.
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		$id = (int)$id;
		$member = \Model_Member::check_authority($id);
		$member_profiles = \Model_MemberProfile::get4member_id($member->id, true);
		$data = array(
			'is_mypage' => true,
			'access_from' => 'self',
			'member' => $member,
			'member_profiles' => $member_profiles,
			'hide_fallow_btn' => true,
		);
		$this->set_title_and_breadcrumbs($member->name.' さんの詳細', array('admin/member' => term('member.view', 'site.management')));
		$this->template->subtitle = \View::forge('member/_parts/detail_subtitle', array('member' => $member));
		$this->template->content = \View::forge('member/index', $data);
	}

	/**
	 * News delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		$id = (int)$id;
		\Util_security::check_method('POST');
		\Util_security::check_csrf();

		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			$member = \Model_Member::check_authority($id);
			$message = \Site_Member::remove($member);
			\Session::set_flash('message', $message);
		}
		catch(\EmailValidationFailedException $e)
		{
			\Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
			$error_message = 'メール送信エラー';
		}
		catch(\EmailSendingFailedException $e)
		{
			\Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
			$error_message = 'メール送信エラー';
		}
		catch(\Auth\SimpleUserUpdateException $e)
		{
			$is_transaction_rollback = true;
			$error_message = term('member.view').'が存在しません。';
		}
		catch(\Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = \Site_Controller::get_error_message($e, true);
		}
		catch(\FuelException $e)
		{
			$is_transaction_rollback = true;
			if (!$error_message = $e->getMessage()) $error_message = term('site.left').'に失敗しました。';
		}
		if ($error_message)
		{
			if ($is_transaction_rollback && \DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $error_message);
		}

		\Response::redirect(\Site_Util::get_redirect_uri('admin/member'));
	}

	/**
	 * Admin member invite
	 * 
	 * @access  public
	 * @return  Response
	 */
	//public function action_invite($group = null)
	public function action_invite()
	{
		if (!conf('member.inviteFromAdmin.isEnabled', 'admin')) throw new \HttpNotFoundException();

		$val = self::get_validation_invite();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$success_message = __('message_send_mail', array('label' => t('invite')));
			$error_message = '';
			$is_transaction_rollback = false;
			try
			{
				if (!$val->run()) throw new \ValidationFailedException($val->show_errors());
				$post = $val->validated();
				if (\Model_MemberPre::get_one4invite_member_id_and_email(\Site_Member::get_admin_member_id(), $post['email']))
				{
					throw new \ValidationFailedException(__('message_invite_mail_already_sent'));
				}

				\DB::start_transaction();
				$posted_group = conf('member.inviteFromAdmin.isEnabled', 'admin') ? $post['group'] : conf('group.defaultValue', 'member');
				$token = \Model_MemberPre::save_with_token($post['email'], null, \Site_Member::get_admin_member_id(), $posted_group);
				\DB::commit_transaction();

				$mail = new \Site_Mail('memberInvite');
				$mail->send($post['email'], array(
					'register_url' => sprintf('%s?token=%s', \Uri::create('member/register'), $token),
					'invite_member_name' => FBD_ADMIN_NAME,
					'invite_message' => $post['message'],
				));

				\Session::set_flash('message', $success_message);
				\Response::redirect('admin/member/invite');
			}
			catch(\ValidationFailedException $e)
			{
				$error_message = \Site_Controller::get_error_message($e);
			}
			catch(\EmailValidationFailedException $e)
			{
				\Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
				$error_message = __('message_send_mail_error');
			}
			catch(\EmailSendingFailedException $e)
			{
				Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
				$error_message = __('message_send_mail_error');
			}
			catch(\Database_Exception $e)
			{
				$is_transaction_rollback = true;
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(\FuelException $e)
			{
				$is_transaction_rollback = true;
				$error_message = \Site_Controller::get_error_message($e);
			}
			if ($is_transaction_rollback && \DB::in_transaction()) \DB::rollback_transaction();
			if ($error_message) \Session::set_flash('error', $error_message);
		}

		$this->set_title_and_breadcrumbs(
			sprintf('%sを%s', t('member.view'), t('form.do_invite')),
			array('admin/member' => term('member.view', 'site.management'))
		);
		$this->template->content = \View::forge('member/invite', array(
			'val' => $val,
			'is_set_group' => true,
			'member_pres' => \Model_MemberPre::get4invite_member_id(\Site_Member::get_admin_member_id())
		));
	}

	private static function get_validation_invite()
	{
		$val = \Validation::forge('invite');

		if (conf('member.inviteFromAdmin.selectGroup.isEnabled', 'admin'))
		{
			$options = \Site_Member::get_group_options(conf('member.inviteFromAdmin.selectGroup.options', 'admin'));
			$val->add('group', term('member.group.view'), array('options' => $options, 'type' => 'select'))
				->add_rule('required')
				->add_rule('in_array', array_keys($options));
		}

		$email = \Form_Util::get_model_field('member_auth', 'email');
		$val->add('email', t('member.email'), $email['attributes'], $email['rules']);

		$val->add('message', term('common.message'), array(
			'rows' => 3,
			'placeholder' => term('form.invite', 'common.message', 'form._not_required'),
		), array('trim'));

		return $val;
	}
}
