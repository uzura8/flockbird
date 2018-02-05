<?php
namespace Contact;

class Controller_Contact extends \Controller_Site
{
	protected $check_not_auth_action = array();
	protected $val;
	protected $member_email;

	public function before()
	{
		parent::before();

		$this->check_registered_email_and_redirect();
	}

	protected function check_registered_email_and_redirect()
	{
		if (!empty($this->u->member_auth->email))
		{
			$this->member_email = $this->u->member_auth->email;
			return;
		}

		$message = __('message_error_unregisterd_for', array('label' => t('site.email')))
						 . __('message_please_register_for', array('label' => t('site.email')));
		\Session::set_flash('message', $message);
		\Response::redirect('member/setting/email/regist');
	}

	/**
	 * Contact form index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		if (!$this->val) $this->val = self::get_validation_object();
		$this->set_title_and_breadcrumbs(term('contact.view'));
		$this->template->content = \View::forge('index', array('val' => $this->val));
	}

	/**
	 * Contact form confirm
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm()
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();

		if (!$this->val) $this->val = self::get_validation_object();
		if (!$this->val->run())
		{
			\Session::set_flash('error', $this->val->show_errors());
			$this->action_index();
			return;
		}

		$this->set_title_and_breadcrumbs(
			term('contact.view', 'common.content', 'form.confirm'),
			array('contact' => term('contact.view', 'common.content', 'form.input'))
		);
		$this->template->content = \View::forge('confirm', array('val' => $this->val));
	}

	public function action_send()
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();

		if (!$this->val) $this->val = self::get_validation_object();
		if (!$this->val->run())
		{
			\Session::set_flash('error', $this->val->show_errors());
			$this->action_index();
			return;
		}
		$post = $this->val->validated();

		$error_message = '';
		try
		{
			if (!$this->member_email) throw new \FuelException('Email not rgistered');

			$category_num = $post['category'] ?: '';
			$variables = array(
				'to_name' => $this->u->name,
				'body' => $post['body'],
				'category' => t('contact.fields.pre.category.options.'.$category_num),
			);
			// Send to member
			$mail = new \Site_Mail('contactToMember', null, get_member_lang($this->u->id));
			$mail->send($this->member_email, $variables);

			// Send to admin
			$mail = new \Site_Mail('contactToMember', array(
				'reply_to' => array($this->member_email => $this->u->name),
			));
			$mail->send(FBD_ADMIN_MAIL, $variables, true, 'ja');

			\Session::set_flash('message', __('message_send_mail', array('label' => t('contact.view'))));
			\Response::redirect('member');
		}
		catch(\EmailValidationFailedException $e)
		{
			\Util_Toolkit::log_error('send mail error: '.__METHOD__.' validation error');
			$error_message = __('message_send_mail_error');
		}
		catch(\EmailSendingFailedException $e)
		{
			\Util_Toolkit::log_error('send mail error: '.__METHOD__.' sending error');
			$error_message = __('message_send_mail_error');
		}
		catch(\Exception $e)
		{
			if (!$error_message = $e->getMessage())
			{
				$error_message = __('message_send_failed_for', array('label' => t('site.mail')));
			}
		}

		\Session::set_flash('error', $error_message);
		$this->action_index();
	}

	private static function get_validation_object($form_name = '')
	{
		$val = \Validation::forge($form_name);

		if ($confs = conf('contact.fields.pre', 'contact'))
		{
			$val = Site_Util::set_form_fields($val, $confs);
		}
		if ($confs = conf('contact.fields.default', 'contact'))
		{
			$val = Site_Util::set_form_fields($val, $confs);
		}
		if ($confs = conf('contact.fields.post', 'contact'))
		{
			$val = Site_Util::set_form_fields($val, $confs);
		}
		
		return $val;
	}
}

