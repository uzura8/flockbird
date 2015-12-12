<?php
class Controller_Auth extends Controller_Site
{
	protected $check_not_auth_action = array(
		'login',
		'logout',
		'callback',
	);

	private $_config = null;
	private $_salt_length = null;

	public function before()
	{
		if (!conf('auth.isEnabled')) throw new \HttpNotFoundException();
		parent::before();

		if(!isset($this->_config))
		{
			$this->_config = Config::load('opauth', 'opauth');
		}
	}

	/**
	 * The login.
	 * 
	 * @access  public
	 * @return  Response or void
	 */
	public function action_login($_provider = null, $method = null)
	{
		// Already logged in
		Auth::check() and Response::redirect('member');

		if ($_provider) return $this->opauth_login_start($_provider, $method);

		$destination = Session::get_flash('destination') ?: Input::post('destination', '');

		if (Input::method() == 'POST')
		{
			try
			{
				Util_security::check_csrf();
				if (!$this->login_val->run()) throw new FuelException($this->login_val->show_errors());
				$post = $this->login_val->validated();
				$posted_email = Arr::get($post, \Config::get('uzuraauth.username_post_key'));
				$posted_password = Arr::get($post, \Config::get('uzuraauth.password_post_key'));

				$auth = Auth::instance();
				// account lock check.
				if ($auth->check_is_account_locked($posted_email))
				{
					throw new FuelException('アカウントがロックされています');
				}
				// login check.
				if (!Auth::check() && !$auth->login($posted_email, $posted_password))
				{
					throw new FuelException;
				}

				// does the user want to be remembered?
				if (Input::param('rememberme', false))
				{
					// create the remember-me cookie
					Auth::remember_me();
				}
				else
				{
					// delete the remember-me cookie if present
					Auth::dont_remember_me();
				}

				// credentials ok, go right in
				return $this->login_succeeded($destination);
			}
			catch(FuelException $e)
			{
				$this->login_failed(false, $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs('ログイン');
		$this->template->content = View::forge('auth/_parts/login', array('destination' => $destination));
	}

	protected function force_login($member_id)
	{
		$auth = Auth::instance();
		$auth->logout();
		if (!$auth->force_login($member_id))
		{
			throw new FuelException('Member login failed.');
		}

		return true;
	}

	/**
	 * The logout action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_logout()
	{
		Auth::dont_remember_me();
		Auth::logout();
		Session::set_flash('message', 'ログアウトしました');
		Response::redirect('auth/login');
	}

	public function opauth_login_start($_provider = null, $method = null)
	{
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

	protected function login_failed($is_redirect = true, $message = null)
	{
		Session::set_flash('error', $message ?: 'ログインに失敗しました');
		if ($is_redirect) Response::redirect(conf('login_uri.site'));
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
		$this->force_login($member_oauth->member_id);
		if (conf('auth.oauth.forceSetRememberMe')) Auth::remember_me();

		return $this->login_succeeded();
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
		if ($service_url = $this->get_service_url($provider, $response)) $input['service_url'] = $service_url;

		try
		{
			$member_oauth = Model_MemberOauth::forge();
			$val = Validation::forge('provider_signup');
			$val->add_model($member_oauth);
			$val->fieldset()->field('member_id')->delete_rule('required');
			if (!$val->run($input)) throw new \FuelException($val->show_errors());
			$input = $val->validated();

			$provider_id = Model_OauthProvider::get_id($provider);
			\DB::start_transaction();
			$member = Model_Member::forge();
			$member->name = str_replace(' ', '', $input['service_name']);
			$member->group = conf('group.options.user', 'member');
			$member->status = conf('status.options.normal', 'member');
			list($member->sex, $member->sex_public_flag) = Site_Oauth::get_sex($response, $provider);
			list($member->birthyear, $member->birthyear_public_flag) = Site_Oauth::get_birthyear($response, $provider);
			list($member->birthday, $member->birthday_public_flag) = Site_Oauth::get_birthday($response, $provider);
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

			if (!empty($response['auth']['info']['email']))
			{
				Model_Memberauth::save_email($response['auth']['info']['email'], $member->id);
			}
			if (conf('auth.oauth.saveTermsUnAgreement'))
			{
				Model_MemberConfig::set_value($member->id, 'terms_un_agreement', 1);
			}
			// timeline 投稿
			if (is_enabled('timeline')) \Timeline\Site_Model::save_timeline($member->id, null, 'member_register', $member->id, $member->created_at);
			\DB::commit_transaction();

			if (!empty($response['auth']['info']['image']))
			{
				$this->save_profile_image($response['auth']['provider'], $response['auth']['info']['image'], $member);
			}
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			if (conf('auth.oauth.log.isOutputErrorLog.provider_signup'))
			{
				\Util_Toolkit::log_error('OAuth provider_signup error: '. isset($e) ? $e->getMessage() : '');
			}

			return $this->login_failed();
		}
		$this->force_login($member->id);
		if (conf('auth.oauth.forceSetRememberMe')) Auth::remember_me();

		return $this->login_succeeded();
	}

	protected function save_profile_image($provider, $image_url, $member_obj)
	{
		$image_url = $this->get_profile_image_url($provider, $image_url);
		$save_file_path_tmp = sprintf('%stmp/%s_%s_%s', APPPATH, $member_obj->id, Util_string::get_unique_id(), time());
		Site_Upload::save_image_from_url($image_url, $save_file_path_tmp, conf('upload.types.img.types.m.max_size', null, 0));
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

	protected static function get_service_url($provider, $response)
	{
		if ($provider == 'Google' && !empty($response['auth']['raw']['link']))
		{
			return $response['auth']['raw']['link'];
		}
		elseif (!empty($response['auth']['info']['urls'][strtolower($provider)]))
		{
			return $response['auth']['info']['urls'][strtolower($provider)];
		}

		return false;
	}
}
