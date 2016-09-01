<?php

class Controller_Member_Invite extends Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber leave
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$val = self::get_validation_object();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$success_message = __('message_send_mail', array('label' => t('invite')));
			$error_message = '';
			$is_transaction_rollback = false;
			try
			{
				if (!$val->run()) throw new ValidationFailedException($val->show_errors());
				$post = $val->validated();
				if (Model_MemberPre::get_one4invite_member_id_and_email($this->u->id, $post['email']))
				{
					throw new ValidationFailedException(__('message_invite_mail_already_sent'));
				}

				DB::start_transaction();
				$token = Model_MemberPre::save_with_token($post['email'], null, $this->u->id);
				DB::commit_transaction();

				$mail = new Site_Mail('memberInvite');
				$mail->send($post['email'], array(
					'register_url' => sprintf('%s?token=%s', Uri::create('member/register'), $token),
					'invite_member_name' => $this->u->name,
					'invite_message' => $post['message'],
				));

				Session::set_flash('message', $success_message);
				Response::redirect('member/invite');
			}
			catch(ValidationFailedException $e)
			{
				$error_message = Site_Controller::get_error_message($e);
			}
			catch(EmailValidationFailedException $e)
			{
				Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
				$error_message = __('message_send_mail_error');
			}
			catch(EmailSendingFailedException $e)
			{
				Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
				$error_message = __('message_send_mail_error');
			}
			catch(\Database_Exception $e)
			{
				$is_transaction_rollback = true;
				$error_message = Site_Controller::get_error_message($e, true);
			}
			catch(FuelException $e)
			{
				$is_transaction_rollback = true;
				$error_message = Site_Controller::get_error_message($e);
			}
			if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
			if ($error_message) Session::set_flash('error', $error_message);
		}

		$this->set_title_and_breadcrumbs(term('form.invite_friend'), null, $this->u);
		$this->template->content = \View::forge('member/invite', array(
			'val' => $val,
			'member_pres' => Model_MemberPre::get4invite_member_id($this->u->id)
		));
	}

	private static function get_validation_object()
	{
		$val = Validation::forge('invite');

		$email = Form_Util::get_model_field('member_auth', 'email');
		$email['attributes']['placeholder'] = __('invite_form_email_placeholder');
		$val->add('email', t('member.email'), $email['attributes'], $email['rules']);

		$val->add('message', term('common.message'), array(
			'rows' => 3,
			'placeholder' => __('invite_form_message_placeholder'),
		), array('trim'));

		return $val;
	}
}
