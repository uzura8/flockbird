<?php

namespace MyAuth;

/**
 * UzuraAuth basic login driver
 *
 * @package     Fuel
 * @subpackage  Auth
 */
class Auth_Login_Uzuraauth extends Auth_Login_Driver
{

	public static function _init()
	{
		\Config::load('uzuraauth', true);

		// setup the remember-me session object if needed
		if (\Config::get('uzuraauth.remember_me.enabled', false))
		{
			static::$remember_me = \Session::forge(array(
				'driver' => 'cookie',
				'cookie' => array(
					'cookie_name' => \Config::get('uzuraauth.remember_me.cookie_name', 'rmcookie'),
				),
				'encrypt_cookie' => true,
				'expire_on_close' => false,
				'expiration_time' => \Config::get('uzuraauth.remember_me.expiration', 86400 * 31),
			));
		}
	}

	/**
	 * @var  \Model_Member  user object for the current user
	 */
	protected $member = null;

	/**
	 * @var  array  Simpleauth compatible permissions array for the current logged-in user
	 */
	protected $permissions = array();

	/**
	 * @var  array  UzuraAuth class config
	 */
	protected $config = array(
//		'drivers' => array('group' => array('NormalGroup')),
	);

	/**
	 * Check the user exists
	 *
	 * @return  bool
	 */
	public function validate_user($email = '', $password = '')
	{
		// get the user identification and password
		$email    = trim($email)    ? trim($email)    : trim(\Input::post(\Config::get('uzuraauth.username_post_key', 'email')));
		$password = trim($password) ? trim($password) : trim(\Input::post(\Config::get('uzuraauth.password_post_key', 'password')));

		// and make sure we have both
		if (empty($email) or empty($password))
		{
			return false;
		}

		// account lock check
		if ($this->check_is_account_locked($email)) return false;

		// hash the password
		$password    = $this->hash_password($password, $email);

		// and do a lookup of this user
		$member_auth = \Model_MemberAuth::query()
			->where('email', $email)
			->where('password', $password)
			->get_one();

		if (!$member_auth) return false;

		$member = self::get_member4id($member_auth->member_id);

		// return the user object, or false if not found
		return $member ?: false;
	}

	/**
	 * Login user
	 *
	 * @param   string
	 * @param   string
	 * @return  bool
	 */
	public function login($email = '', $password = '')
	{
		if ( ! ($this->member = $this->validate_user($email, $password)))
		{
			// force a logout
			$this->logout();

			// and signal a failed login
			return false;
		}

		// register so Auth::logout() can find us
		\Auth\Auth::_register_verified($this);

		// store the logged-in user and it's hash in the session
		//\Session::set('username', $this->user->username);
		\Session::set('member_id', $this->member->id);
		\Session::set('login_hash', $this->create_login_hash());

		// reset login failed count.
		if (\Config::get('uzuraauth.accountLock.isEnabled')) \Session::delete('login_failed');

		// and rotate the session id, we've elevated rights
		\Session::instance()->rotate();

		return true;
	}

	/**
	 * Force login user
	 *
	 * @param   string
	 * @return  bool
	 */
	public function force_login($member_id = '')
	{
		// bail out if we don't have a user
		if (empty($member_id))
		{
			return false;
		}

		// get the user we need to login
		if ( ! $member_id instanceOf \Model_Member)
		{
			$this->member = self::get_member4id($member_id);
		}
		else
		{
			$this->member = $member_id;
		}

		// did we find it
		if ($this->member and ! $this->member->is_new())
		{
			// store the logged-in user and it's hash in the session
			//\Session::set('username', $this->user->username);
			\Session::set('member_id', $this->member->id);
			\Session::set('login_hash', $this->create_login_hash());

			// reset login failed count.
			if (\Config::get('uzuraauth.accountLock.isEnabled')) \Session::delete('login_failed');

			// and rotate the session id, we've elevated rights
			\Session::instance()->rotate();

			// register so Auth::logout() can find us
			\Auth\Auth::_register_verified($this);

			return true;
		}

		// force a logout
		$this->logout();

		// and signal a failed login
		return false;
	}

	/**
	 * Logout user
	 *
	 * @return  bool
	 */
	public function logout()
	{
		// reset the current user
		if (\Config::get('uzuraauth.guest_login', true))
		{
			//$this->user = \Model\Auth_User::query()
			//	->where('id', '=', 0)
			//	->get_one();
		}
		else
		{
			$this->member = false;
		}

		// delete the session data identifying this user
		//\Session::delete('username');
		\Session::delete('member_id');
		\Session::delete('login_hash');

		return true;
	}

	/**
	 * Create new user
	 *
	 * @param   string  must contain valid email address
	 * @param   string
	 * @param   string
	 * @param   int     group id: now unuse.
	 * @param   Array: now unuse.
	 * @return  bool
	 */
	public function create_user($email, $password, $name = '', $group = 1, Array $profile_fields = array())
	{
		// prep the password
		$password = trim($password);

		// and validate the email address
		$email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

		// bail out if we're missing username, password or email address
		if (empty($password) or empty($email))
		{
			throw new \SimpleUserUpdateException('Email address or password is not given, or email address is invalid', 1);
		}

		// check if we already have an account with this email address or username
		$duplicate = \Model_MemberAuth::query()->where('email', $email)->get_one();

		// did we find one?
		if ($duplicate)
		{
			// bail out with an exception
			if (strtolower($email) == strtolower($duplicate->email))
			{
				throw new \SimpleUserUpdateException('Email address already exists', 2);
			}
		}

		// do we have a logged-in user?
		if ($currentuser = \Auth::get_user_id())
		{
			$currentuser = $currentuser[1];
		}
		else
		{
			$currentuser = 0;
		}

		// create the new user record
		try
		{
			$member = \Model_Member::forge();
			if (strlen($name)) $member->name = $name;
			$member->register_type  = 0;
			$member->filesize_total = 0;
			$member->sex_public_flag = 0;
			$member->birthyear_public_flag = 0;
			$member->birthday_public_flag = 0;
			$member->invite_member_id = 0;
			$result = $member->save();

			$member_auth = \Model_MemberAuth::forge();
			$member_auth->member_id = $member->id;
			$member_auth->email     = $email;
			$member_auth->password  = $this->hash_password((string) $password, $email);
			$result = $member_auth->save() && $result;
		}
		catch (\Exception $e)
		{
			$result = false;
		}
		$this->member = $member;

		// and the id of the created user, or false if creation failed
		return $result ? $member->id : false;
	}

	/**
	 * Update a user's properties
	 * Note: Username cannot be updated, to update password the old password must be passed as old_password
	 *
	 * @param   Array  properties to be updated including profile fields
	 * @param   integer
	 * @return  bool
	 */
	public function update_user($values, $member_id = null)
	{
		if (empty($member_id)) $member_id = $this->member->id;
		if (empty($member_id))
		{
			throw new \SimpleUserUpdateException('Username not found', 4);
		}

		// get the current user record
		$current_member = self::get_member4id($member_id);
		if (!$current_member_auth = $current_member->member_auth)
		{
			$current_member_auth = \Model_MemberAuth::forge();
			$current_member_auth->member_id = $member_id;
		}

		// and bail out if it doesn't exist
		if (empty($current_member))
		{
			throw new \SimpleUserUpdateException('Username not found', 4);
		}

		// validate the values passed and assume the update array
		$update = array();
		if (array_key_exists('email', $values))
		{
			$email = filter_var(trim($values['email']), FILTER_VALIDATE_EMAIL);
			if ( ! $email)
			{
				throw new \SimpleUserUpdateException('Email address is not valid', 7);
			}

			$matches = \Model_MemberAuth::query()
				->where('email', '=', $email)
				->where('member_id', '!=', $current_member->id)
				->get_one();

			if ($matches)
			{
				throw new \SimpleUserUpdateException('Email address is already in use', 11);
			}

			$update['email'] = $email;
			unset($values['email']);
		}
		$new_email_for_salt = array_key_exists('email', $update) ? $update['email'] : $current_member_auth->email;

		if (array_key_exists('password', $values))
		{
			if (empty($values['old_password'])
				or $current_member_auth->password != $this->hash_password(trim($values['old_password']), $current_member_auth->email))
			{
				throw new \SimpleUserWrongPassword('Old password is invalid');
			}

			$password = trim(strval($values['password']));
			if ($password === '')
			{
				throw new \SimpleUserUpdateException('Password can\'t be empty.', 6);
			}
			$update['password'] = $this->hash_password($password, $new_email_for_salt);
			unset($values['password']);
		}
		if (array_key_exists('old_password', $values))
		{
			unset($values['old_password']);
		}

		// load the updated values into the object
		$current_member_auth->from_array($update);

		$updated = false;

		// any values remaining?
		if ( ! empty($values))
		{
			// set them as EAV values
			foreach ($values as $key => $value)
			{
				if ( ! isset($current_member_auth->{$key}) or $current_member_auth->{$key} != $value)
				{
					if ($value === null)
					{
						unset($current_member_auth->{$key});
					}
					else
					{
						$current_member_auth->{$key} = $value;
					}

					// mark we've updated something
					$updated = true;
				}
			}
		}

		// check if this has changed anything
		if ($updated or $updated = $current_member_auth->is_changed())
		{
			// and only save if it did
			$current_member_auth->save();
		}

		// return the updated status
		return $updated;
	}

	/**
	 * Change a user's password
	 *
	 * @param   string
	 * @param   string
	 * @param   string  username or null for current user
	 * @return  bool
	 */
	public function change_password($old_password, $new_password, $member_id = null)
	{
		try
		{
			return (bool) $this->update_user(array('old_password' => $old_password, 'password' => $new_password), $member_id);
		}
		// Only catch the wrong password exception
		catch (\SimpleUserWrongPassword $e)
		{
			return false;
		}
	}

	/**
	 * Generates new random password, sets it for the given username and returns the new password.
	 * To be used for resetting a user's forgotten password, should be emailed afterwards.
	 *
	 * @param   string  $username
	 * @return  string
	 */
	public function reset_password($member_id)
	{
		$new_password = \Str::random('alnum', 8);
		$this->change_password_simple($member_id, $new_password);

		return $new_password;
	}

	public function change_password_simple($member_id, $new_password)
	{
		if (!$member_id || !$member = self::get_member4id($member_id))
		{
			throw new \SimpleUserUpdateException('Failed to reset password, user was invalid.', 8);
		}
		if (!$member_auth = $member->member_auth)
		{
			throw new \SimpleUserUpdateException('Member_id was invalid.');
		}
		$member_auth->password = $this->hash_password($new_password, $member_auth->email);
		if (!$result = $member_auth->save())
		{
			throw new \SimpleUserUpdateException('Failed to reset password, member_id was invalid.');
		}

		return $new_password;
	}

	/**
	 * Deletes a given user
	 *
	 * @param   string
	 * @return  bool
	 */
	public function delete_user($member_id)
	{
		// make sure we have a user to delete
		if (empty($member_id))
		{
			throw new \Auth\SimpleUserUpdateException('Cannot delete user with empty username', 9);
		}

		// get the user object
		$member = self::get_member4id($member_id);

		// if it was found, delete it
		if ($member)
		{
			return (bool) $member->delete();
		}
		return false;
	}

	/**
	 * Creates a temporary hash that will validate the current login
	 *
	 * @return  string
	 */
	public function create_login_hash()
	{
		// we need a logged-in user to generate a login hash
		if (empty($this->member))
		{
			throw new \SimpleUserUpdateException('User not logged in, can\'t create login hash.', 10);
		}

		// set the previous and current last login
		$this->member->previous_login = $this->member->last_login;
		$last_login = date('Y-m-d H:i:s');
		$this->member->last_login = $last_login;

		// generate the new hash
		$this->member->login_hash = sha1(\Config::get('uzuraauth.login_hash_salt').$this->member->id.$last_login);

		// store it
		$this->member->save();

		// and return it
		return $this->member->login_hash;
	}

	/**
	 * Get the member object
	 *
	 * @return  mixed  Model_Member object, or false if no user is set
	 */
	public function get_member()
	{
		return empty($this->member) ? false : $this->member;
	}

	/**
	 * Get the member's ID
	 *
	 * @return  integer
	 */
	public function get_member_id()
	{
		if (empty($this->member))
		{
			return false;
		}

		return (int)$this->member->id;
	}

	/**
	 * Get the user's ID
	 *
	 * @return  Array  containing this driver's ID & the user's ID
	 */
	public function get_user_id()
	{
		if (empty($this->member))
		{
			return false;
		}

		return array($this->id, (int) $this->member->id);
	}

	/**
	 * Get the user's groups
	 *
	 * @return  Array  containing the group driver ID & the user's group ID
	 */
//	public function get_groups()
//	{
//		// bail out if we don't have a user group to return
//		if (empty($this->user))
//		{
//			return false;
//		}
//
//		return array(array('Ormgroup', $this->user->group));
//	}

	/**
	 * Get the user's emailaddress
	 *
	 * @return  string
	 */
	public function get_email()
	{
		if (empty($this->member) || empty($this->member->member_auth))
		{
			return false;
		}

		return $this->member->member_auth->email;
	}

	/**
	 * Get the user's screen name
	 *
	 * @return  string
	 */
	public function get_screen_name()
	{
		if (empty($this->member))
		{
			return term('guest');
		}

		return $this->member->name;
	}

	/**
	 * Extension of base driver method to default to user group instead of user id
	 */
//	public function has_access($condition, $driver = null, $user = null)
//	{
//		if (is_null($user))
//		{
//			if ( ! is_array($groups = $this->get_groups()))
//			{
//				return false;
//			}
//			$user = reset($groups);
//		}
//		return parent::has_access($condition, $driver, $user);
//	}

	/**
	 * Check for login
	 *
	 * @return  bool
	 */
	protected function perform_check()
	{
		// get the username and login hash from the session
		$member_id  = \Session::get('member_id');
		$login_hash = \Session::get('login_hash');

		// only worth checking if there's both a member_id and login-hash
		if (!empty($member_id) and !empty($login_hash))
		{
			// if we don't have a user, or we're logging in from guest mode
			if (is_null($this->member) or ($this->member->id != $member_id and $this->member->id == 0))
			{
				$this->member = self::get_member4id($member_id);
			}

			// return true when login was verified, and either the hash matches or multiple logins are allowed
			if ($this->member and (\Config::get('uzuraauth.multiple_logins', false) or $this->member->login_hash === $login_hash))
			{
				return true;
			}
		}

		// not logged in, do we have remember-me active and a stored member_id?
		elseif (static::$remember_me and $member_id = static::$remember_me->get('member_id', null))
		{
			return $this->force_login($member_id);
		}

		// force a logout
		$this->logout();

		return false;
	}

	/**
	 * Set a remember-me cookie for the passed user id, or for the current
	 * logged-in user if no id was given
	 *
	 * @return  bool  wether or not the cookie was set
	 */
	public function remember_me($member_id = null)
	{
		// if no user-id is given, get the current user's id
		if ($member_id === null and isset($this->member->id))
		{
			$member_id = $this->member->id;
		}

		// if we have a session and an id, set it
		if (static::$remember_me and $member_id)
		{
			static::$remember_me->set('member_id', $member_id);
			return true;
		}

		// remember-me not enabled, or no user id available
		return false;
	}

	/**
	 * Check password
	 *
	 * @param   string
	 * @return  bool
	 */
	public function check_password($password = '')
	{
		if (!$this->perform_check()) return false;

		$member_id = \Session::get('member_id');
		$password = trim($password) ? trim($password) : trim(\Input::post(\Config::get('uzuraauth.password_post_key', 'password')));
		if (empty($member_id) || empty($password)) return false;

		if (!$member = self::get_member4id($member_id)) return false;
		if (!$member->member_auth->password) return false;

		return $member->member_auth->password == $this->hash_password($password, $member->member_auth->email);
	}

	private static function get_member4id($id)
	{
		return \Model_Member::find($id, array('rows_limit' => 1, 'related' => 'member_auth'));
	}

	private static function get_member_id4email($email)
	{
		if (!$member_auth = \Model_MemberAuth::query()->where('email', $email)->get_one()) return false;

		return $member_auth->member_id;
	}

	/**
	 * Account locked check
	 *
	 * @return  bool
	 */
	public function check_is_account_locked($email)
	{
		if (!\Config::get('uzuraauth.accountLock.isEnabled')) return false;
		$login_failed_info = \Session::get('login_failed', array());
		if (!$login_failed_info = \Session::get('login_failed', array())) return false;

		if (!isset($login_failed_info[$email]['last_execute_time'])) return false;
		if (\Util_Date::check_is_passed($login_failed_info[$email]['last_execute_time'], \Config::get('uzuraauth.accountLock.recoveryTime')))
		{
			$this->reset_account_lock_count($email);
			return false;
		}

		if (!isset($login_failed_info[$email]['count'])) return false;
		if ($login_failed_info[$email]['count'] < \Config::get('uzuraauth.accountLock.loginFailAcceptCount')) return false;

		return true;
	}

	/**
	 * Set account lock information.
	 *
	 * @return  bool
	 */
	private function countup_account_lock_count($email)
	{
		if (!\Config::get('uzuraauth.accountLock.isEnabled')) return;

		$login_failed_info = \Session::get('login_failed', array());
		$login_failed_count_current = isset($login_failed_info[$email]['count']) ? $login_failed_info[$email]['count'] : 0;
		$login_failed_count_current++;
		\Session::set('login_failed', array(
			$email => array(
				'count' => $login_failed_count_current,
				'last_execute_time' => \Date::time()->get_timestamp(),
			),
		));

		if ($login_failed_count_current >= \Config::get('uzuraauth.accountLock.loginFailAcceptCount'))
		{
			if (\Config::get('uzuraauth.accountLock.isLogging')) \Util_Toolkit::log_error('account_lock: '.$email);
			$this->sent_noticication_mail($email);
		}

		return $login_failed_count_current;
	}

	/**
	 * Reset account lock information.
	 *
	 * @return  bool
	 */
	private function reset_account_lock_count($email)
	{
		if (!\Config::get('uzuraauth.accountLock.isEnabled')) return;

		\Session::set('login_failed', array(
			$email => array(
				'count' => 0,
				'last_execute_time' => null,
			),
		));
	}

	/**
	 * Sent account lock notificaton mail.
	 *
	 * @return  bool
	 */
	private function sent_noticication_mail($email)
	{
		if ($notification_mail_info = \Config::get('uzuraauth.accountLock.isSentNotificationMail', array())) return false;
		if (empty($notification_mail_info['member']) && empty($notification_mail_info['admin'])) return false;

		$send_mails = array();
		$member = null;
		if ($admin_mails = \Config::get('uzuraauth.accountLock.isSentNotificationEmail.admin')) $send_mails = $admin_mails;
		if (\Config::get('uzuraauth.accountLock.isSentNotificationEmail.member') && $member_id = static::get_member_id4email($email))
		{
			$member = static::get_member4id($id);
			$send_mails[] = $email;
		}

		$maildata = array(
			'from_name' => \Config::get('mail.member_setting_common.from_name'),
			'from_address' => \Config::get('mail.member_setting_common.from_mail_address'),
		);
		$maildata['member_id'] = $member ? $member->id : '';
		$maildata['member_name'] = $member ? $member->name : '';
		foreach ($send_mails as $send_mail)
		{
			$maildata['to_address'] = $send_mail;
			$maildata['is_admin'] = in_array($send_mail, $admin_mails);
			if (!$maildata['is_admin'] && $member) $maildata['to_name'] = $member->name;
			try
			{
				$this->send_account_lock_mail($maildata);
			}
			catch(EmailValidationFailedException $e)
			{
				\Util_Toolkit::log_error('account_lock_mail_error: email validation error');
			}
			catch(EmailSendingFailedException $e)
			{
				\Util_Toolkit::log_error('account_lock_mail_error: email sending error');
			}
			catch(FuelException $e)
			{
				\Util_Toolkit::log_error('account_lock_mail_error');
			}
		}
	}

	private function send_account_lock_mail($data)
	{
		$data['subject'] = 'アカウントがロックされました';
		$data['body'] = <<< END
アカウントがロックされました。

====================
ログインに失敗したメールアドレス: {$data['login_failed_email']}
====================

END;
		if ($is_admin)
		{
			$data['body'] .= <<< END
管理者向け情報
====================
member_id: {$data['member_id']}
ニックネーム: {$data['member_name']}
====================

END;
		}

		Util_toolkit::sendmail($data);
	}
}

// end of file uzuraauth.php
