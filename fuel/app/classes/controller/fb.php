<?php
require_once APPPATH.'vendor/facebook-php-sdk/facebook.php';

use Model\Fbuser;

class Controller_Fb extends Controller_Site
{
	private $fb;

	public function before()
	{
		parent::before();
		if (!PRJ_FACEBOOK_APP_ID) throw new HttpNotFoundException;

		Config::load('facebook', 'facebook');
		$this->fb = new Facebook(Config::get('facebook.init'));
	}

	public function action_index()
	{
		$this->template->title = 'Index » Index';

		$is_login = $this->fb->getUser()?true:false;
		$data = array(
			'is_login' => $is_login,
		);

		if($is_login and Input::method() == 'POST')
		{
			$v = Validation::forge();
			$v->add('message', 'message')->add_rule('required');
			if(!$v->run())
			{
				Session::set_flash('message', $v->errors('message')->get_message());
			}
			else
			{
				$message = $v->validated('message');
				try
				{
					$res = $this->fb->api(array(
						'method' => 'stream.publish',
						'message' => $message,
					));
					Session::set_flash('message', 'complete!!');
				}
				catch (FacebookApiException $e)
				{
					Session::set_flash('message', $e->getMessage());
				}
				Response::redirect('fb/index/');
			}
		}

		$this->template->content = View::forge('fb/index',$data);
	}

	public function action_login()
	{
		$url = $this->fb->getLoginUrl(Config::get('facebook.login'));
		Response::redirect($url);
	}

	public function action_callback()
	{
		try
		{
			$fb = $this->fb->api('/me');
			$is_save = false;
			if (!$user = Fbuser::find_by_facebook_id($fb['id']))
			{
				$member_id = $this->create_member_from_facebook($fb['id'], $fb['name']);
				$user = new Fbuser;
				$user->member_id = $member_id;
				$user->facebook_id   = $fb['id'];
				$user->facebook_name = $fb['name'];
				$user->facebook_link = $fb['link'];
				$is_save = true;
			}
			else
			{
				if ($user->facebook_name != $fb['name'])
				{
					$user->facebook_name = $fb['name'];
					$is_save = true;
				}
				if ($user->facebook_link != $fb['link'])
				{
					$user->facebook_link = $fb['link'];
					$is_save = true;
				}
			}
			if ($is_save) $user->save();

			$this->login($user->member_id);
			Session::set_flash('message', 'ログインしました');
			Response::redirect('member');
		}
		catch (Orm\ValidationFailed $e)
		{
			throw new Exception($e->getMessage());
		}
		catch (FacebookApiException $e)
		{
			throw new Exception($e->getMessage());
		}
	}

	public function action_logout()
	{
		$url = $this->fb->getLogoutUrl(Config::get('facebook.logout'));
		if (Config::get('facebook.is_destory_facebook_session')) $this->fb->destroySession();
		Auth::logout();
		Session::set_flash('message', 'ログアウトしました');
		Response::redirect($url);
	}

	private function create_member_from_facebook($facebook_id, $name)
	{
		if (!$member_id = Model_Member::create_member_from_facebook($facebook_id, $name))
		{
			throw new Exception('Create member failed.');
		}

		return $member_id;
	}

	private function login($member_id)
	{
		$auth = Auth::instance();
		$auth->logout();
		if (!$auth->force_login($member_id))
		{
			throw new Exception('Member login failed.');
		}

		return true;
	}
}
