<?php

class Controller_Member_profile extends Controller_Member
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
	}

	/**
	 * Mmeber_profile index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->template->title = 'マイホーム';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			'プロフィール' => '',
		);

		$this->template->content = View::forge('member/profile/index');
	}

	/**
	 * Mmeber_profile setting_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_setting_image()
	{
		$this->template->title = 'マイホーム';
		$this->template->header_title = site_title();
		$this->template->breadcrumbs = array(
			Config::get('site.term.toppage') => '/',
			Config::get('site.term.myhome') => '/member/',
			'プロフィール' => '/member/profile/',
			'プロフィール写真設定' => '',
		);

		$this->template->content = View::forge('member/profile/setting_image');
	}

	/**
	 * Mmeber_profile edit_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_edit_image()
	{
		if (Input::method() == 'POST')
		{
			$config = array(
				'path'   => PRJ_UPLOAD_DIR.'/img/member/original',
				'prefix' => sprintf('m_%d_', $this->current_user->id),
			);
			Upload::process($config);
			if (count(Upload::get_files()) == 1 && Upload::is_valid())
			{
				Util_security::check_csrf();

				Upload::save(0);
				$file = Upload::get_files(0);

				/**
				 * ここで$fileを使ってアップロード後の処理
				 * $fileの中にいろんな情報が入っている
				 **/

				Session::set_flash('message', '写真を更新しました。');
			}
			else
			{
				Session::set_flash('error', Upload::get_errors());
			}
		}

		Response::redirect('member/profile/setting_image');
	}
}
