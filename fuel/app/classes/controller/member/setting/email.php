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
		list($mode, $is_registerd, $is_regist_mode, $action_name) = $this->get_common_vals($mode);
		$this->set_validation_email($this->u->check_registered_oauth(true));

		$this->set_title_and_breadcrumbs(
			term('site.email').$action_name,
			$is_regist_mode ? array() : array('member/setting' => term('site.setting', 'form.update')),
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
		list($mode, $is_registerd, $is_regist_mode, $action_name, $is_oauth_registerd_user) = $this->get_common_vals($mode);

		if (is_null(Input::post('code')))
		{
			$this->set_validation_email($is_oauth_registerd_user);

			$error_message = '';
			$is_transaction_rollback = false;
			$term_mail = term('site.mail');
			$message = sprintf("確認用{$term_mail}を送信しました。受信した{$term_mail}内に記載された%sを入力してください。", term('form.confirm', 'site.code'));
			try
			{
				if (!$this->val_obj->run()) throw new ValidationFailedException($this->val_obj->show_errors());
				$post = $this->val_obj->validated();
				if (!$is_oauth_registerd_user && !$this->auth_instance->check_password())
				{
					throw new ValidationFailedException(term('site.password').'が正しくありません');
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
				$error_message = 'メール送信エラー';
			}
			catch(EmailSendingFailedException $e)
			{
				Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
				$error_message = 'メール送信エラー';
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
		$middle_breadcrumbs = $is_regist_mode ?
			array('member/setting/email/regist' => term('site.email', 'site.registration')) :
			array(
				'member/setting' => term('site.setting', 'form.update'),
				'member/setting/email' => term('site.email', 'form.update'),
			);
		$this->set_title_and_breadcrumbs(term('site.email', $action_name, 'form.confirm'), $middle_breadcrumbs, $is_regist_mode ? null : $this->u);
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
		list($mode, $is_registerd, $is_regist_mode, $action_name, $is_oauth_registerd_user) = $this->get_common_vals($mode);

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
				throw new ValidationFailedException(term('site.password').'が正しくありません');
			}

			$member_email_pre = Model_MemberEmailPre::get4member_id($this->u->id);
			$code_error_message = sprintf('%sが正しくないか、%sが過ぎてます。再度%sを%sしてください。',
				term('form.confirm', 'site.code'), term('form.enabled', 'common.timelimit'), term('form.for_confirm', 'site.mail'), term('form.send'));
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
			$member_email_pre->delete();// 仮登録情報の削除
			DB::commit_transaction();
			$this->set_current_user();

			$mail = new Site_Mail('memberRegisterEmailConfirm');
			$mail->send($email, array('to_name' => $this->u->name));

			Session::set_flash('message', sprintf('%sを%sしました。', term('site.email'), $action_name));
			Response::redirect('member/setting');
		}
		catch(ValidationFailedException $e)
		{
			$error_message = $e->getMessage();
		}
		catch(EmailValidationFailedException $e)
		{
			Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
			$error_message = 'メール送信エラー';
		}
		catch(EmailSendingFailedException $e)
		{
			Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
			$error_message = 'メール送信エラー';
		}
		catch(\Auth\SimpleUserUpdateException $e)
		{
			$is_transaction_rollback = true;
			$error_message = term('site.email').'の変更に失敗しました。';
		}
		catch(\Database_Exception $e)
		{
			$is_transaction_rollback = true;
			$error_message = \Site_Controller::get_error_message($e, true);
		}
		catch(FuelException $e)
		{
			$is_transaction_rollback = true;
			if (!$error_message = $e->getMessage()) $error_message = sprintf('%sの%sに失敗しました。', term('site.email'), $action_name);
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
		$action_name = term($is_registerd ? 'form.update' : 'site.registration');
		$is_oauth_registerd_user = $this->u->check_registered_oauth(true);

		return array($mode, $is_registerd, $is_regist_mode, $action_name, $is_oauth_registerd_user);
	}

	private function set_validation_email($is_not_check_password = false)
	{
		if ($this->val_obj) return;

		$this->val_obj = Validation::forge('setting_email');

		$email_field = Form_Util::get_model_field('member_auth', 'email');
		$this->val_obj->add('email', $email_field['label'], $email_field['attributes'], $email_field['rules']);
		$this->val_obj->fieldset()->field('email')->delete_rule('unique');

		$this->val_obj->add('email_confirm', term('site.email', 'form._confirm'), $email_field['attributes'], $email_field['rules'])
				->add_rule('match_field', 'email');
		$this->val_obj->fieldset()->field('email_confirm')->delete_rule('unique');

		if (!$is_not_check_password)
		{
			$email_field = Form_Util::get_model_field('member_auth', 'password');
			$this->val_obj->add('password', $email_field['label'], $email_field['attributes'], $email_field['rules']);
		}
	}

	private function set_validation_code()
	{
		$field = Form_Util::get_model_field('member_email_pre', 'code');
		$this->val_obj->add('code', $field['label'], $field['attributes'], $field['rules']);
		$this->val_obj->set_message('valid_string', term('form.confirm', 'site.code').'が正しくありません。');
		$this->val_obj->set_message('exact_length', term('form.confirm', 'site.code').'が正しくありません。');
	}

	private function check_email_registered($posted_email, $mode, $dummy_message = '', $is_error_message = false)
	{
		if (!empty($this->u->member_auth->email) && $this->u->member_auth->email == $posted_email)
		{
			throw new ValidationFailedException(sprintf('その%sは現在登録済みです。', term('site.email')));
		}

		$term_mail = term('site.mail');
		if (Model_MemberAuth::get4email($posted_email))
		{
			if (conf('member.setting.email.hideUniqueCheck'))
			{
				Session::set_flash($is_error_message ? 'error' : 'message', $dummy_message);
				return false;
			}

			throw new ValidationFailedException(sprintf('その%sは登録できません。', term('site.email')));
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
