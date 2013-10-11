<?php
class Controller_Auth extends Controller_Site
{
	protected $check_not_auth_action = array(
		'login',
	);

	private $_config = null;
	private $_salt_length = null;
	private $_iteration_count = null;

	public function before()
	{
		if(!isset($this->_config))
		{
			$this->_config = Config::load('opauth', 'opauth');
		}

		$this->_salt_length = 32;
		$this->_iteration_count = 10;
	}

	public function action_login($_provider = null, $method = null)
	{
		// 引数無し時は通常ログイン
		if (is_null($_provider)) Response::redirect('site/login');

		// http://domainname/auth/login/twitter/oauth_callback?denied=signature
		if ($method === 'oauth_callback')
		{
			if (Input::get('denied'))
			{
				return $this->login_failed();
			}
		}

		if(!array_key_exists(Inflector::humanize($_provider), Arr::get($this->_config, 'Strategy')))
		{
			return $this->login_failed();
		}

		new Opauth($this->_config, true);
	}

	// Twitter / Facebook ログイン成功/失敗時に呼ばれる
	public function action_callback()
	{
		$_opauth = new Opauth($this->_config, false);

		switch($_opauth->env['callback_transport'])
		{
			case 'session':
				session_start();
				$response = $_SESSION['opauth'];
				unset($_SESSION['opauth']);
				break;
		}

		if (array_key_exists('error', $response))
		{
			return $this->login_failed();
		}
		if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid']))
		{
			return $this->login_failed();
		}
		elseif (!$_opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason))
		{
			return $this->login_failed();
		}

		// OAuth ログイン成功
		return $this->opauth_login($response);
	}

	public function opauth_login($response = null)
	{
		$provider = $response['auth']['provider'];

		return $this->provider_login($provider, $response);
	}

	public function login_succeeded($member_id)
	{
		$this->login($member_id);
		Session::set_flash('message', 'ログインしました');
		Response::redirect('member');
	}

	public function provider_login($provider, $response = null)
	{
		$uid = $response['auth']['uid'];
		$provider_id = Model_OauthProvider::get_id($provider);
		$query = Model_MemberOauth::query()->where('oauth_provider_id', $provider_id)->where('uid', $uid);
		if ($query->count() == 0)
		{
			// 未登録の場合はサインアップ
			return $this->provider_signup($provider, $response);
		}
		// 登録済みの場合はログイン
		$member_oauth = $query->get_one();

		return $this->login_succeeded($member_oauth->member_id);
	}

	public function provider_signup($provider, $response = null)
	{
		$service_name = isset($response['auth']['info']['name']) ? $response['auth']['info']['name'] : $response['auth']['info']['nickname'];
		$input = array(
			'uid' => (string) $response['auth']['uid'],
			'token' => $response['auth']['credentials']['token'],
			'service_name' => $response['auth']['info']['name'],
		);
		if (!empty($response['auth']['credentials']['expires'])) $input['expires'] = strtotime($response['auth']['credentials']['expires']);
		if (!empty($response['auth']['info']['urls'][$provider])) $input['service_url'] = $response['auth']['info']['urls'][$provider];
	
		try
		{
			$member_oauth = Model_MemberOauth::forge();
			$val = Validation::forge();
			$val->add_model($member_oauth);
			if (!$val->run($input)) throw new \FuelException($val->show_errors());
			$input = $val->validated();

			$provider_id = Model_OauthProvider::get_id($provider);
			\DB::start_transaction();
			$member = Model_Member::forge();
			$member->name = $input['service_name'];
			$member->filesize_total = 0;
			$member->register_type = $provider_id;
			if ($member->save() === false) throw new \FuelException('Member save failed.');

			$member_oauth->member_id = $member->id;
			$member_oauth->oauth_provider_id = $provider_id;
			$member_oauth->uid = $input['uid'];
			$member_oauth->token = $input['token'];
			$member_oauth->secret = $input['secret'];
			$member_oauth->service_name = $input['service_name'];
			if (!empty($input['expires'])) $member_oauth->expires = $input['expires'];
			if (!empty($input['service_url'])) $member_oauth->service_url = $input['service_url'];
			if ($member_oauth->save() === false) throw new \FuelException('Oauth data save failed.');
			\DB::commit_transaction();

			if (!empty($response['auth']['info']['image']))
			{
				$this->save_profile_image($response['auth']['provider'], $response['auth']['info']['image'], $member);
			}
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();

			return $this->login_failed();
		}

		return $this->login_succeeded($member->id);
	}

	protected function save_profile_image($provider, $image_url, $member_obj)
	{
		$image_url = $this->get_profile_image_url($provider, $image_url);
		$save_file_path_tmp = sprintf('%stmp/%s_%s_%s', APPPATH, $member_obj->id, Util_string::get_unique_id(), time());
		Site_Upload::save_image_from_url($image_url, $save_file_path_tmp, Config::get('site.upload.types.img.types.m.max_size', 0));
		\DB::start_transaction();
		Site_Member::save_profile_image($member_obj, $save_file_path_tmp, false);
		\DB::commit_transaction();
	}

	protected static function get_profile_image_url($provider, $image_url)
	{
		if ($provider == 'Facebook')
		{
			$image_url = str_replace('?type=square', '?type=large', $image_url);
		}
		elseif ($provider == 'Twitter')
		{
			$image_url = str_replace('_normal.JPEG', '.JPEG', $image_url);
		}

		return $image_url;
	}
}
