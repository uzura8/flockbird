<?php

class Controller_Member_Register extends Controller_Site
{
	//public $template = 'admin/template';

	protected $check_not_auth_action = array(
		'index',
		'signup',
		'confirm_signup',
	);

	public function before()
	{
		parent::before();

		if (IS_AUTH) Response::redirect('member');
	}

	/**
	 * Execute index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		if (!$member_pre = $this->check_token())
		{
			Session::set_flash('error', __('message_invalid_url'));
			throw new HttpNotFoundException;
		}

		$form_member_profile = new Form_MemberProfile('regist', $member_pre->group);
		$add_fields = array();
		$add_fields['token']    = Form_Util::get_model_field('member_pre', 'token');
		$add_fields['password'] = Form_Util::get_model_field('member_auth', 'password');
		if (!$member_pre->password)
		{
			$add_fields['password_confirm'] = Form_Util::get_model_field('member_auth', 'password', term('site.password', 'form._confirm'));
		}
		$form_member_profile->set_validation($add_fields, 'member_register');

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$error_message = '';
			$is_transaction_rollback = false;
			try
			{
				$form_member_profile->validate();
				$post = $form_member_profile->get_validated_values();
				if ($member_pre->password && $post['password'] != $member_pre->password)
				{
					throw new ValidationFailedException(__('message_invalid_for', array('label' => t('site.password'))));
				}

				DB::start_transaction();
				// create new member
				$auth = Auth::instance();
				if (!$member_id = $auth->create_user($member_pre->email, $post['password'], $post['member_name']))
				{
					throw new FuelException('create member error.');
				}
				$member = $auth->get_member();
				// Delete pre data
				if ($member_pre->invite_member_id || $member_pre->group)
				{
					if ($member_pre->invite_member_id) $member->invite_member_id = $member_pre->invite_member_id;
					if ($member_pre->group) $member->group = $member_pre->group;
					$member->save();
					// TODO: make friend to invited_member
				}
				$email    = $member_pre->email;
				$password = $member_pre->password;
				$member_pre->delete();

				// Register member_profile
				$form_member_profile->set_member_obj($member);
				$form_member_profile->seve();

				// Register lang setting
				if (is_enabled_i18n()) Model_MemberConfig::set_value($member->id, 'lang', Site_Lang::get_client_lang(true));

				// Delete member_pre duplicated email
				if ($member_pres = \Model_MemberPre::query()->where('email', $email)->get())
				{
					foreach ($member_pres as $member_pre) $member_pre->delete();
				}

				// Post timeline
				if (is_enabled('timeline')) \Timeline\Site_Model::save_timeline($member_id, null, 'member_register', $member_id, $member->created_at);
				DB::commit_transaction();

				$mail = new Site_Mail('memberRegister', null, get_member_lang($member_id));
				$mail->send($member_pre->email, array('to_name' => $member->name));

				if ($auth->login($email, $password))
				{
					Session::set_flash('message', __('message_registered_complete'));
					Response::redirect('member');
				}
				Session::set_flash('error', __('site_message_login_failed'));
				Response::redirect(conf('login_uri.site'));
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
			catch(\Auth\SimpleUserUpdateException $e)
			{
				$is_transaction_rollback = true;
				$error_message = __('message_disabled_to_register_for', array('label' => t('site.email')));
			}
			catch(\Database_Exception $e)
			{
				$is_transaction_rollback = true;
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(FuelException $e)
			{
				$is_transaction_rollback = true;
				$error_message = Site_Controller::get_error_message($e);
			}
			if ($error_message)
			{
				if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
				Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(__('member_registration'), array('member/register/signup' => term('site.signup')));
		$this->template->content = View::forge('member/register/index', array(
			'val' => $form_member_profile->get_validation(),
			'member_public_flags' => $form_member_profile->get_member_public_flags(),
			'profiles' => $form_member_profile->get_profiles(),
			'member_profile_public_flags' => $form_member_profile->get_member_profile_public_flags(),
			'member_pre' => $member_pre,
		));
	}

	/**
	 * Mmeber signup
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_signup()
	{
		if (! conf('member.register.signup.IsEnabled')) throw new \HttpNotFoundException;

		$val = self::get_form_signup(Model_MemberAuth::forge());
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
			}
			catch(\FuelException $e)
			{
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(term('site.signup'));
		$this->template->content = \View::forge('member/register/signup', array('val' => $val));
	}

	/**
	 * Execute confirm signup
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm_signup()
	{
		if (! conf('member.register.signup.IsEnabled')) throw new \HttpNotFoundException;
		Util_security::check_method('POST');
		Util_security::check_csrf();

		if (!$form = Fieldset::instance('confirm_signup')) $form = $this->get_form_signup_confirm();
		$val = $form->validation();
		$val->fieldset()->field('email')->delete_rule('unique');

		$redirect_uri = conf('login_uri.site');
		$success_message = __('member_message_signup_complete');
		$error_message = '';
		$is_transaction_rollback = false;
		try
		{
			if (!$val->run()) throw new \FuelException($val->show_errors());
			$post = $val->validated();

			if (Model_MemberAuth::get4email($post['email']))
			{
				if (conf('member.register.email.hideUniqueCheck'))
				{
					Session::set_flash('message', $success_message);
					Response::redirect($redirect_uri);
				}
				throw new FuelException(__('message_disabled_to_register_for', array('label' => t('site.email'))));
			}

			DB::start_transaction();
			$token = Model_MemberPre::save_with_token($post['email'], $post['password']);
			DB::commit_transaction();

			$mail = new Site_Mail('memberSignup');
			$mail->send($post['email'], array(
				'register_url' => sprintf('%s?token=%s', Uri::create('member/register'), $token),
			));

			Session::set_flash('message', $success_message);
			Response::redirect($redirect_uri);
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
			$error_message = \Site_Controller::get_error_message($e, true);
		}
		catch(FuelException $e)
		{
			$is_transaction_rollback = true;
			$error_message = $e->getMessage();
		}

		if ($is_transaction_rollback && DB::in_transaction()) DB::rollback_transaction();
		Session::set_flash('error', $error_message);
		$this->action_signup();
	}

	private static function get_form_signup()
	{
		$member_auth = Model_MemberAuth::forge();
		$val = Validation::forge();
		$val->add_model($member_auth);

		$val->fieldset()->field('email')->set_attribute('placeholder', 'sample@example.com');

		$password_min_length_list = $member_auth::get_property_value('password', 'validation.min_length');
		$val->fieldset()->field('password')->set_attribute(
			'placeholder',
			t('form.characters_or_more', array('num' => reset($password_min_length_list)))
		);

		return $val;
	}

	public function get_form_signup_confirm()
	{
		return Site_Util::get_form_instance('confirm_signup', Model_MemberAuth::forge(), true, null, 'submit');
	}

	private function check_token()
	{
		if (!$member_pre = Model_MemberPre::get4token(Input::param('token'))) return false;
		if (!Site_Util::check_token_lifetime($member_pre->updated_at, conf('member.register.token_lifetime'))) return false;

		return $member_pre;
	}
}
