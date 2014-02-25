<?php

class Controller_Member extends Controller_Site
{
	protected $check_not_auth_action = array(
		'home',
		'resend_password',
		'confirm_reset_password',
		'reset_password',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
	}

	/**
	 * Mmeber profile
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_home($id = null)
	{
		$id = (int)$id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($id);
		list($list, $is_next) = \Timeline\Site_Model::get_list(Auth::check() ? $this->u->id : 0, $id, $is_mypage);

		$this->set_title_and_breadcrumbs($member->name.' さんのページ');
		$this->template->subtitle = View::forge('_parts/home_subtitle', array('member' => $member));
		$this->template->post_footer = \View::forge('timeline::_parts/load_timelines');
		$this->template->content = \View::forge('member/home', array('member' => $member, 'list' => $list, 'is_next' => $is_next));
	}

	/**
	 * Mmeber resend password
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_resend_password()
	{
		if (!$form = Fieldset::instance('resend_password'))
		{
			$form = $this->form_resend_password();
		}

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}
		$this->set_title_and_breadcrumbs('パスワードの再設定');
		$this->template->content = View::forge('member/resend_password');
		$this->template->content->set_safe('html_form', $form->build('/member/confirm_reset_password'));// form の action に入る
	}

	/**
	 * Confirm resend password
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm_reset_password()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		Util_security::check_method('POST');
		Util_security::check_csrf();

		$form = $this->form_resend_password();
		$val  = $form->validation();

		if (!$val->run())
		{
			Session::set_flash('error', $val->show_errors());
			$this->action_resend_password();
			return;
		}
		$post = $val->validated();

		$message = 'パスワードのリセット方法をメールで送信しました。';
		if (!$member_auth = Model_MemberAuth::query()->where('email', $post['email'])->related('member')->get_one())
		{
			Session::set_flash('message', $message);
			Response::redirect(Config::get('site.login_uri.site'));
			return;
		}

		try
		{
			$data = array();
			$data['member_id'] = $member_auth->member_id;
			$data['email']     = $post['email'];

			$maildata = array();
			$maildata['to_name']      = $member_auth->member->name;
			$maildata['to_address']   = $post['email'];
			$maildata['from_name']    = \Config::get('site.member_setting_common.from_name');
			$maildata['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
			$maildata['subject']      = \Config::get('site.member_resend_password.subject');
			$maildata['token']        = $this->save_member_password_pre($data);
			$this->send_confirm_reset_password_mail($maildata);

			Session::set_flash('message', $message);
			Response::redirect(Config::get('site.login_uri.site'));
		}
		catch(EmailValidationFailedException $e)
		{
			$this->display_error('パスワードのリセット: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
		}
		catch(EmailSendingFailedException $e)
		{
			$this->display_error('パスワードのリセット: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
		}
	}

	/**
	 * Execute reset password.
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_reset_password()
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		$member_password_pre = Model_MemberPasswordPre::query()->where('token', Input::param('token'))->related('member')->get_one();
		if (!$member_password_pre || $member_password_pre->created_at < date('Y-m-d H:i:s', strtotime('-1 day')))
		{
			$this->display_error('メンバー登録: 不正なURL');
			return;
		}

		$form = $this->form_reset_password();
		$val  = $form->validation();

		if (Input::method() == 'POST')
		{
			Util_security::check_csrf();
			$auth = Auth::instance();

			if ($val->run())
			{
				$post = $val->validated();
				try
				{
					if (!$auth->change_password_simple($member_password_pre->member_id, $post['password']))
					{
						throw new Exception('change password error.');
					}

					$maildata = array();
					$maildata['from_name']    = \Config::get('site.member_setting_common.from_name');
					$maildata['from_address'] = \Config::get('site.member_setting_common.from_mail_address');
					$maildata['subject']      = \Config::get('site.member_reset_password.subject');
					$maildata['to_address']   = $member_password_pre->email;
					$maildata['to_name']      = $member_password_pre->member->name;
					$maildata['password']    = $post['password'];
					$this->send_reset_password_mail($maildata);

					// 仮登録情報の削除
					$member_password_pre->delete();

					$auth->login($member_password_pre->email, $post['password']);
					Session::set_flash('message', 'パスワードを登録しました。');
					Response::redirect('member');
				}
				catch(EmailValidationFailedException $e)
				{
					$this->display_error('メンバー登録: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
					return;
				}
				catch(EmailSendingFailedException $e)
				{
					$this->display_error('メンバー登録: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
					return;
				}
				catch(Auth\SimpleUserUpdateException $e)
				{
					Session::set_flash('error', 'パスワードの登録に失敗しました。');
				}
			}
			else
			{
				if ($val->show_errors())
				{
					Session::set_flash('error', $val->show_errors());
				}
				else
				{
					Session::set_flash('error', 'パスワードが正しくありません');
				}
			}
		}

		$this->set_title_and_breadcrumbs('パスワードの再登録');
		$data = array('val' => $val, 'member_password_pre' => $member_password_pre);
		$this->template->content = View::forge('member/reset_password', $data);
		$this->template->content->set_safe('html_form', $form->build('/member/reset_password'));// form の action に入る
	}

	public function form_resend_password()
	{
		$add_fields = array(
			'email' => array(
				'label' => 'メールアドレス',
				'attributes' => array('type'=>'email', 'class' => 'form-control input-xlarge'),
				'rules' => array('trim', 'required', 'valid_email'),
			),
		);
		$form = \Site_Util::get_form_instance('resend_password', null, true, $add_fields, array('value' => term('form.submit')));

		return $form;
	}

	public function form_reset_password()
	{
		$add_fields = array(
			'password' => array(
				'label' => '新しいパスワード',
				'attributes' => array('type'=>'password', 'class' => 'form-control input-xlarge'),
				'rules' => array('trim', 'required', array('min_length', 6),  array('max_length', 20)),
			),
			'password_confirm' => array(
				'label' => '新しいパスワード(確認用)',
				'attributes' => array('type'=>'password', 'class' => 'form-control input-xlarge'),
				'rules' => array('trim', 'required', array('match_field', 'password')),
			),
			'token' => array(
				'attributes' => array('type'=>'hidden', 'value' => Input::param('token')),
				'rules' => array('required'),
			),
		);
		$form = \Site_Util::get_form_instance('reset_password', null, true, $add_fields, array('value' => '変更'));

		return $form;
	}

	private function save_member_password_pre($data)
	{
		$member_password_pre = Model_MemberPasswordPre::find($data['member_id']);
		if (!$member_password_pre) $member_password_pre = new Model_MemberPasswordPre;

		$member_password_pre->member_id = $data['member_id'];
		$member_password_pre->email  = $data['email'];
		$member_password_pre->token     = Util_toolkit::create_hash();
		$member_password_pre->save();

		return $member_password_pre->token;
	}

	private function send_confirm_reset_password_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$register_url = sprintf('%s?token=%s', uri::create('member/reset_password'), $data['token']);
		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
こんにちは、{$data['to_name']}さん

{$site_name} は、あなたのアカウントのパスワードをリセットするように依頼を受けました。

パスワードをリセットしたい場合、下記のリンクをクリックしてください (もしくは、URLをコピペしてブラウザに入力してください)。
{$register_url}

パスワードをリセットしたくない場合は、このメッセージを無視してください。 パスワードはリセットされません。

END;

		util_toolkit::sendmail($data);
	}

	private function send_reset_password_mail($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$site_name = PRJ_SITE_NAME;

		$data['body'] = <<< END
{$data['to_name']} さん

パスワードを再登録しました。

================================
新しいパスワード: {$data['password']}
================================

END;

		util_toolkit::sendmail($data);
	}
}
