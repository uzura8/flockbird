<?php

class Controller_Member_Setting_Email extends Controller_Member
{
	protected $check_not_auth_action = array();

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
	public function action_index()
	{
		$val = self::get_validation_email();
		$is_registerd = !empty($this->u->member_auth->email);

		if (\Input::method() == 'POST')
		{
			Util_security::check_csrf();

			$error_message = '';
			$is_transaction_rollback = false;
			try
			{
				if (!$val->run()) throw new ValidationFailedException($val->show_errors());
				$post = $val->validated();
				$this->check_email_registered($post['email']);

				DB::start_transaction();
				$member_email_pre = Model_MemberEmailPre::save_with_token($this->u->id, $post['email']);
				DB::commit_transaction();

				$mail = new Site_Mail('memberRegisterEmailConfirm');
				$mail->send($post['email'], array(
					'to_name' => $this->u->name,
					'confirmation_code' => $member_email_pre->code,
				));

				$term_mail = term('site.mail');
				$message = sprintf("確認用{$term_mail}を送信しました。受信した{$term_mail}内に記載された%sを入力してください。", term('form.confirm', 'site.code'));
				Session::set_flash('message', $message);
				Response::redirect('member/setting/email/register');
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
			}
		}

		$this->set_title_and_breadcrumbs(
			term('site.email', $is_registerd ? 'form.update' : 'site.registration'),
			array('member/setting' => term('site.setting', 'form.update')),
			$this->u
		);
		$this->template->content = View::forge('member/setting/email/index', array('val' => $val));
	}

	/**
	 * Execute register email.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_register()
	{
		if (!$member_email_pre = Model_MemberEmailPre::get4member_id($this->u->id))
		{
			throw new HttpNotFoundException;
		}
		$email = $member_email_pre->email;
		$val = self::get_validation_code();
		$is_registerd = !empty($this->u->member_auth->email);
		$action_name = term($is_registerd ? 'form.update' : 'form.registration');

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$error_message = '';
			$is_transaction_rollback = false;
			try
			{
				if (!$val->run()) throw new ValidationFailedException($val->show_errors());
				$post = $val->validated();
				$this->check_email_registered($email);

				if (!self::check_confirmation_code($member_email_pre, $post['code']))
				{
					$message = sprintf('%sが正しくないか、%sが過ぎてます。再度%sを%sしてください。',
										term('form.confirm', 'site.code'), term('form.enabled', 'common.timelimit'), term('form.for_confirm', 'site.mail'), term('form.send'));
					throw new ValidationFailedException($message);
				}

				DB::start_transaction();
				if (!$this->auth_instance->update_user(array('email' => $email))) throw new FuelException('Change email error.');
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
		}

		$this->set_title_and_breadcrumbs(
			term('site.email', $is_registerd ? 'form.update' : 'site.registration', 'form.confirm'),
			array('member/setting' => term('site.setting', 'form.update'),
			'member/setting/email' => term('site.email', $is_registerd ? 'form.update' : 'site.registration')),
			$this->u
		);

		$this->template->content = View::forge('member/setting/email/register', array(
			'val' => $val,
			'email' => $email,
			'is_registerd' => $is_registerd,
		));
	}

	private function get_validation_email()
	{
		$val = Validation::forge('setting_email');

		$email_field = Form_Util::get_model_field('member_auth', 'email');
		$val->add('email', $email_field['label'], $email_field['attributes'], $email_field['rules']);
		$val->fieldset()->field('email')->delete_rule('unique');

		$val->add('email_confirm', term('site.email', 'form._confirm'))
				->add_rule('required')
				->add_rule('match_field', 'email');

		return $val;
	}

	private function get_validation_code()
	{
		$val = Validation::forge('register_email');
		$field = Form_Util::get_model_field('member_email_pre', 'code');
		$val->add('code', $field['label'], $field['attributes'], $field['rules']);
		$val->set_message('valid_string', term('form.confirm', 'site.code').'が正しくありません。');
		$val->set_message('exact_length', term('form.confirm', 'site.code').'が正しくありません。');

		return $val;
	}

	private function check_email_registered($posted_email)
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
				$message = sprintf("確認用{$term_mail}を送信しました。受信した{$term_mail}内に記載された URL より%sの登録を完了してください。", term('site.email'));
				Session::set_flash('message', $message);
				Response::redirect('member/setting');
			}

			throw new ValidationFailedException(sprintf('その%sは登録できません。', term('site.email')));
		}
	}

	private static function check_confirmation_code(Model_MemberEmailPre $member_email_pre, $code)
	{
		if (!Site_Util::check_token_lifetime($member_email_pre->updated_at, conf('member.setting.email.codeLifetime'))) return false;
		if (empty($member_email_pre->code)) return false;
		if ($member_email_pre->code != $code) return false;

		return true;
	}
}
