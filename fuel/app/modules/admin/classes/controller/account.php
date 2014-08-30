<?php
namespace Admin;

class Controller_Account extends Controller_Admin
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Admin account index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$users = Model_AdminUser::query()->get();
		$this->template->subtitle = \View::forge('account/_parts/list_subtitle');
		$this->set_title_and_breadcrumbs(term('admin.account.view', 'site.management'));
		$this->template->content = \View::forge('account/list', array('list' => $users));
	}

	/**
	 * Admin account list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		$this->action_index();
	}

	/**
	 * Admin account create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$val = \Validation::forge();
		$val->add_model(Model_AdminUser::forge());

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				$auth = \Auth::instance();
				if (!$user_id = $auth->create_user($post['username'], $post['password'], $post['email'], $post['group']))
				{
					throw new \FuelException('create member error.');
				}

				if ($post['email'])
				{
					$maildata = array();
					$maildata['from_name']    = \Config::get('mail.admin.common.from_name');
					$maildata['from_address'] = \Config::get('mail.admin.common.from_mail_address');
					$maildata['subject']      = \Config::get('mail.admin.create_user.subject');
					$maildata['to_address']   = $post['email'];
					$maildata['to_name']      = $post['username'];
					$maildata['password']     = $post['password'];
					$this->send_mail_create_user($maildata);
				}

				\Session::set_flash('message', term('admin.account.view', 'form.create').'が完了しました。');
				\Response::redirect('admin/account');
			}
			catch(\EmailValidationFailedException $e)
			{
				$this->display_error('メンバー登録: 送信エラー', __METHOD__.' email validation error: '.$e->getMessage());
				return;
			}
			catch(\EmailSendingFailedException $e)
			{
				$this->display_error('メンバー登録: 送信エラー', __METHOD__.' email sending error: '.$e->getMessage());
				return;
			}
			catch(\Auth\SimpleUserUpdateException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(term('admin.account.view', 'form.create'), array('admin/account' => term('admin.account.view')));
		$this->template->content = \View::forge('account/create', array('val' => $val));
	}

	private function send_mail_create_user($data)
	{
		if (!is_array($data)) $data = (array)$data;

		$data['body'] = <<< END
メンバー登録が完了しました。

====================
ユーザ名: {$data['to_name']}
メールアドレス: {$data['to_address']}
仮パスワード: {$data['password']}
====================
※パスワードはログイン後、変更してください。

END;

		\Util_toolkit::sendmail($data);
	}

	/**
	 * Admin account delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		if (check_original_user($id, true))
		{
			throw new \HttpForbiddenException;
		}
		$user = Model_AdminUser::check_authority($id);

		try
		{
			$auth = \Auth::instance();
			\DB::start_transaction();
			$auth->delete_user($user->username);
			\DB::commit_transaction();
			\Session::set_flash('message', term('admin.user.view').'を削除しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect(\Site_Util::get_redirect_uri('admin/account'));
	}
}
