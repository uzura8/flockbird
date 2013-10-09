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
			// echo '<strong style="color: red;">Authentication error: </strong> Opauth returns error auth response.'."<br>\n";
		}
		if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid']))
		{
			return $this->login_failed();
			// echo '<strong style="color: red;">Invalid auth response: </strong>Missing key auth response components.'."<br>\n";
		}
		elseif (!$_opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason))
		{
			return $this->login_failed();
			// echo '<strong style="color: red;">Invalid auth response: </strong>'.$reason.".<br>\n";
		}

		// OAuth ログイン成功
		// return Response::forge(var_dump($response));
		return $this->opauth_login($response);
	}

	public function opauth_login($response = null)
	{
		$provider = $response['auth']['provider'];
		if ($provider === 'Facebook') return $this->facebook_login($response);
		if ($provider === 'Twitter') return $this->twitter_login($response);
	}

	public function login_succeeded($member_id)
	{
		$this->login($member_id);
		Session::set_flash('message', 'ログインしました');
		Response::redirect('member');
	}

	public function facebook_login($response = null)
	{
		$uid = $response['auth']['uid'];
		$query = Model_MemberOauth::query()->where('oauth_provider_id', 1)->where('uid', $uid);
		if ($query->count() == 0)
		{
			// FacebookUser未登録の場合はサインアップ
			return $this->facebook_signup($response);
		}

		// FacebookUser登録済みの場合はログイン
		$member_oauth = $query->get_one();

		return $this->login_succeeded($member_oauth->member_id);
	}

	public function facebook_signup($response = null)
	{
		$input = array(
			'uid' => (string) $response['auth']['uid'],
			'token' => $response['auth']['credentials']['token'],
			'expires' => strtotime($response['auth']['credentials']['expires']),
			'service_name' => $response['auth']['info']['name'],
		);
		if (!empty($response['auth']['info']['urls']['facebook'])) $input['service_url'] = $response['auth']['info']['urls']['facebook'];
	
		try
		{
			$member_oauth = Model_MemberOauth::forge();
			$val = \Validation::forge();
			$val->add_model($member_oauth);
			if (!$val->run($input)) throw new \FuelException($val->show_errors());
			$input = $val->validated();

			\DB::start_transaction();
			$member = Model_Member::forge();
			$member->name = $input['service_name'];
			$member->filesize_total = 0;
			$member->register_type = 1;
			if ($member->save() === false) throw new \FuelException('Member save failed.');

			$member_oauth->member_id = $member->id;
			$member_oauth->oauth_provider_id = 1;
			$member_oauth->uid = $input['uid'];
			$member_oauth->token = $input['token'];
			$member_oauth->secret = $input['secret'];
			$member_oauth->expires = $input['expires'];
			$member_oauth->service_name = $input['service_name'];
			if (!empty($input['service_url'])) $member_oauth->service_url = $input['service_url'];
			if ($member_oauth->save() === false) throw new \FuelException('Oauth data save failed.');
			\DB::commit_transaction();

			if (!empty($response['auth']['info']['image']))
			{
				$image_url = $response['auth']['info']['image'];
				if ($response['auth']['provider'] == 'Facebook')
				{
					$image_url = 'http://graph.facebook.com/'.$input['uid'].'/picture?type=large';
				}
				$save_file_path_tmp = sprintf('%stmp/%s_%s_%s', APPPATH, $member->id, Util_string::get_unique_id(), time());
				Site_Upload::save_image_from_url($image_url, $save_file_path_tmp, Config::get('site.upload.types.img.types.m.max_size', 0));
				\DB::start_transaction();
				Site_Member::save_profile_image($member, $save_file_path_tmp, false);
				\DB::commit_transaction();
			}
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();

			return $this->login_failed();
		}

		return $this->login_succeeded($member->id);
	}

	public function twitter_login($response = null)
	{
		$uid = (string) $response['auth']['uid'];
		$query = Model_TwitterUser::query()->where('uid', $uid);
		if ($query->count() == 0)
		{
			// TwitterUser未登録の場合はサインアップ
			return $this->twitter_signup($response);
		}

		// TwitterUser登録済みの場合はログイン
		$twitter_user = $query->get_one();
		return $this->login_succeeded($twitter_user->user_id);
	}

	public function twitter_signup($response = null)
	{
		// バリデーション
		$val = Model_TwitterUser::validate('create');
		$input = array(
			'uid' => (string) $response['auth']['uid'],
			'token' => $response['auth']['credentials']['token'],
			'secret' => $response['auth']['credentials']['secret'],
		);
	
		if ($val->run($input))
		{
			// バリデーション成功時
			$user = Model_User::forge(array(
				'nickname' => $response['auth']['info']['nickname'],
				'last_login' => \Date::time()->get_timestamp(),
			));
			$twitter_user = Model_TwitterUser::forge($input);

			if ($user and $twitter_user)
			{
				// ユーザー生成成功
				try
				{
					\DB::start_transaction();
					if ($user->save() === false)
					{
						// User保存失敗
						throw new \Exception('user save failed.');
					}
						
					$twitter_user->user_id = $user->id;
					if ($twitter_user->save() === false)
					{
						// TwitterUser保存失敗
						throw new \Exception('twitter_user save failed.');
					}

					// UserとTwitterUserの保存成功
					\DB::commit_transaction();
					return $this->login_succeeded($user->id);
				}
				catch (\Exception $e)
				{
					\DB::rollback_transaction();
					return $this->login_failed();
				}

			}
			else
			{
				// ユーザー生成失敗
				return $this->login_failed();
			}

		}
		else
		{
			// バリデーション失敗時
			return $this->login_failed();
		}
	}
}
