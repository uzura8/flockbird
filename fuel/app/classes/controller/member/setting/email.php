<?php

class Controller_Member_Setting_Email extends Controller_Member
{
	protected $check_not_auth_action = array();
	protected $val_obj;

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber setting email
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index($mode = null)
	{
		list($mode, $is_registerd, $is_regist_mode) = $this->get_common_vals($mode);
		$this->set_validation_email($this->u->check_registered_oauth(true));

		$this->set_title_and_breadcrumbs(
			t($is_registerd ? 'form.change_for' : 'site.registration_for', array('label' => t('member.email'))),
			$is_regist_mode ? array() : array('member/setting' => term('site.setting')),
			$is_regist_mode ? null : $this->u
		);
		$this->template->content = View::forge('member/setting/email/index', array(
			'val' => $this->val_obj,
			'is_regist_mode' => $is_regist_mode,
			'action' => 'member/setting/email/register_confirm/'.$mode,
		));
	}

	/**
	 * Execute register confirm.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_register_confirm($mode = null)
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();
		list($mode, $is_registerd, $is_regist_mode, $is_oauth_registerd_user) = $this->get_common_vals($mode);

		if (is_null(Input::post('code')))
		{
			$this->set_validation_email($is_oauth_registerd_user);

			$error_message = '';
			$is_transaction_rollback = false;
			$message = __('member_message_send_confirmation_code_complete');
			try
			{
				if (!$this->val_obj->run()) throw new ValidationFailedException($this->val_obj->show_errors());
				$post = $this->val_obj->validated();
				if (!$is_oauth_registerd_user && !$this->auth_instance->check_password())
				{
					throw new ValidationFailedException(__('message_invalid_for', array('label' => t('site.password'))));
				}

				if ($this->check_email_registered($post['email'], $mode, $message))
				{
					DB::start_transaction();
					$member_email_pre = Model_MemberEmailPre::save_with_token($this->u->id, $post['email']);
					DB::commit_transaction();

					$mail = new Site_Mail('memberRegisterEmailConfirm');
					$mail->send($post['email'], array(
						'to_name' => $this->u->name,
						'confirmation_code' => $member_email_pre->code,
					));
				}
				Session::set_flash('message', $message);
			}
			catch(ValidationFailedException $e)
			{
				$error_message = $e->getMessage();
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
			catch(Database_Exception $e)
			{
				$is_transaction_rollback = true;
				$error_message = Site_Controller::get_error_message($e, true);
			}
			catch(FuelException $e)
			{
				$is_transaction_rollback = true;
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
				Session::set_flash('error', $error_message);
				$this->action_index($mode);
				return;
			}
			$this->set_validation_code();
		}

		$action_name = t($is_registerd ? 'form.change_for' : 'site.registration_for', array('label' => t('member.email')));
		$middle_breadcrumbs = $is_regist_mode ?
			array('member/setting/email/regist' => $action_name) :
			array(
				'member/setting' => term('site.setting'),
				'member/setting/email' => $action_name,
			);
		$this->set_title_and_breadcrumbs(
			t('form.confirm_for', array('label' => $action_name)),
			$middle_breadcrumbs,
			$is_regist_mode ? null : $this->u
		);
		$this->template->content = View::forge('member/setting/email/register_confirm', array(
			'val' => $this->val_obj,
			'action' => 'member/setting/email/register/'.$mode,
			'input' => Input::post(),
			'is_registerd' => $is_registerd,
			'is_regist_mode' => $is_regist_mode,
		));
	}

	/**
	 * Execute register email.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_register($mode = null)
	{
		Util_security::check_method('POST');
		Util_security::check_csrf();
		list($mode, $is_registerd, $is_regist_mode, $is_oauth_registerd_user) = $this->get_common_vals($mode);

		$this->set_validation_email($is_oauth_registerd_user);
		$this->set_validation_code();
		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			if (!$this->val_obj->run()) throw new ValidationFailedException($this->val_obj->show_errors());
			$post = $this->val_obj->validated();
			if (!$is_oauth_registerd_user && !$this->auth_instance->check_password())
			{
				throw new ValidationFailedException(__('message_invalid_for', array('label' => t('site.password'))));
			}

			$member_email_pre = Model_MemberEmailPre::get4member_id($this->u->id);
			$code_error_message = __('member_message_error_invalid_confirmation_code');
			$this->check_email_registered($member_email_pre ? $member_email_pre->email : $post['email'], $mode, $code_error_message, true);
			if (!$member_email_pre || !self::check_confirmation_code($member_email_pre, $post['code']))
			{
				throw new ValidationFailedException($code_error_message);
			}

			$email = $member_email_pre->email;
			$values = array('email' => $email);
			if (!$is_oauth_registerd_user)
			{
				$values['password'] = $post['password'];
				$values['old_password'] = $post['password'];
			}
			DB::start_transaction();
			if (!$this->auth_instance->update_user($values, $this->u->id)) throw new FuelException('Change email error.');
			$member_email_pre->delete();// Delete pre data

			DB::commit_transaction();
			$this->set_current_user();

			$mail = new Site_Mail('memberRegisterEmailConfirm');
			$mail->send($email, array('to_name' => $this->u->name));

			Session::set_flash(
				'message',
				__($is_registerd ? 'message_change_complete_for': 'message_registered_for', array('label' => t('member.email')))
			);
			Response::redirect('member/setting');
		}
		catch(ValidationFailedException $e)
		{
			$error_message = $e->getMessage();
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
		catch(\Auth\SimpleUserUpdateException $e)
		{
			$is_transaction_rollback = true;
			$error_message = __('message_change_failed_for', array('label' => t('member.email')));
		}
		catch(\Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = \Site_Controller::get_error_message($e, true);
		}
		catch(FuelException $e)
		{
			$is_transaction_rollback = true;
			if (!$error_message = $e->getMessage())
			{
				$error_message = __($is_registerd ? 'message_change_failed_for': 'message_registered_failed_for', array('label' => t('member.email')));
			}
		}
		if ($error_message)
		{
			if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
			Session::set_flash('error', $error_message);
		}

		$this->action_register_confirm($mode);
	}

	private function get_common_vals($mode = '')
	{
		if ($mode != 'regist') $mode = '';

		$is_registerd = !empty($this->u->member_auth->email);
		$is_regist_mode = $mode == 'regist' && !$is_registerd;
		$is_oauth_registerd_user = $this->u->check_registered_oauth(true);

		return array($mode, $is_registerd, $is_regist_mode, $is_oauth_registerd_user);
	}

	private function set_validation_email($is_not_check_password = false)
	{
		if ($this->val_obj) return;

		$this->val_obj = Validation::forge('setting_email');

		$email_field = Form_Util::get_model_field('member_auth', 'email');
		$this->val_obj->add('email', $email_field['label'], $email_field['attributes'], $email_field['rules']);
		$this->val_obj->fieldset()->field('email')->delete_rule('unique');

		$this->val_obj->add('email_confirm', term('member.email', 'form._confirm'), $email_field['attributes'], $email_field['rules'])
				->add_rule('match_field', 'email');
		$this->val_obj->fieldset()->field('email_confirm')->delete_rule('unique');

		if (!$is_not_check_password)
		{
			$email_field = Form_Util::get_model_field('member_auth', 'password');
			$this->val_obj->add('password', $email_field['label'], $email_field['attributes'], $email_field['rules'])
				->add_rule('required');
		}
	}

	private function set_validation_code()
	{
		$field = Form_Util::get_model_field('member_email_pre', 'code');
		$this->val_obj->add('code', $field['label'], $field['attributes'], $field['rules']);
		$this->val_obj->set_message('valid_string', __('message_invalid_for', array('label' => t('site.confirmation_code'))));
		$this->val_obj->set_message('exact_length', __('message_invalid_for', array('label' => t('site.confirmation_code'))));
	}

	private function check_email_registered($posted_email, $mode, $dummy_message = '', $is_error_message = false)
	{
		if (!empty($this->u->member_auth->email) && $this->u->member_auth->email == $posted_email)
		{
			throw new ValidationFailedException(__('member_message_error_change_email_currently_registered'));
		}

		$term_mail = term('site.mail');
		if (Model_MemberAuth::get4email($posted_email))
		{
			if (conf('member.setting.email.hideUniqueCheck'))
			{
				Session::set_flash($is_error_message ? 'error' : 'message', $dummy_message);
				return false;
			}

			throw new ValidationFailedException(__('message_disabled_to_register_for', array('label' => t('member.email'))));
		}

		return true;
	}

	private static function check_confirmation_code(Model_MemberEmailPre $member_email_pre, $code)
	{
		if (!Site_Util::check_token_lifetime($member_email_pre->updated_at, conf('member.setting.email.codeLifetime'))) return false;
		if (empty($member_email_pre->code)) return false;
		if ($member_email_pre->code != $code) return false;

		return true;
	}
}
