<?php

namespace MyAuth;

class Auth_Login_Simpleauth extends \Auth\Auth_Login_Simpleauth
{
	public function create_user($username, $password, $email, $group = 1, Array $profile_fields = array())
	{
		$password = trim($password);
		$email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

		if (empty($username) or empty($password) or empty($email))
		{
			throw new \SimpleUserUpdateException('Username, password or email address is not given, or email address is invalid', 1);
		}

		$same_users = \DB::select_array(\Config::get('simpleauth.table_columns', array('*')))
			->where('username', '=', $username)
			->or_where('email', '=', $email)
			->from(\Config::get('simpleauth.table_name'))
			->execute(\Config::get('simpleauth.db_connection'));

		if ($same_users->count() > 0)
		{
			if (in_array(strtolower($email), array_map('strtolower', $same_users->current())))
			{
				throw new \SimpleUserUpdateException('Email address already exists', 2);
			}
			else
			{
				throw new \SimpleUserUpdateException('Username already exists', 3);
			}
		}

		$user = array(
			'username'        => (string) $username,
			'password'        => $this->hash_password((string) $password),
			'email'           => $email,
			'group'           => (int) $group,
			'profile_fields'  => serialize($profile_fields),
			'last_login'      => 0,
			'login_hash'      => '',
			'created_at'      => date('Y-m-d H:i:s'),
			'updated_at'      => date('Y-m-d H:i:s'),
		);
		$result = \DB::insert(\Config::get('simpleauth.table_name'))
			->set($user)
			->execute(\Config::get('simpleauth.db_connection'));

		return ($result[1] > 0) ? $result[0] : false;
	}

	public function update_user($values, $username = null)
	{
		$username = $username ?: $this->user['username'];
		$current_values = \DB::select_array(\Config::get('simpleauth.table_columns', array('*')))
			->where('username', '=', $username)
			->from(\Config::get('simpleauth.table_name'))
			->execute(\Config::get('simpleauth.db_connection'));

		if (empty($current_values))
		{
			throw new \SimpleUserUpdateException('Username not found', 4);
		}

		$update = array();
		if (array_key_exists('username', $values))
		{
			throw new \SimpleUserUpdateException('Username cannot be changed.', 5);
		}
		if (array_key_exists('password', $values))
		{
			if (empty($values['old_password'])
				or $current_values->get('password') != $this->hash_password(trim($values['old_password'])))
			{
				throw new \SimpleUserWrongPassword('Old password is invalid');
			}

			$password = trim(strval($values['password']));
			if ($password === '')
			{
				throw new \SimpleUserUpdateException('Password can\'t be empty.', 6);
			}
			$update['password'] = $this->hash_password($password);
			unset($values['password']);
		}
		if (array_key_exists('old_password', $values))
		{
			unset($values['old_password']);
		}
		if (array_key_exists('email', $values))
		{
			$email = filter_var(trim($values['email']), FILTER_VALIDATE_EMAIL);
			if ( ! $email)
			{
				throw new \SimpleUserUpdateException('Email address is not valid', 7);
			}
			$matches = \DB::select()
				->where('email', '=', $email)
				->where('id', '!=', $current_values[0]['id'])
				->from(\Config::get('simpleauth.table_name'))
				->execute(\Config::get('simpleauth.db_connection'));
			if (count($matches))
			{
				throw new \SimpleUserUpdateException('Email address is already in use', 11);
			}
			$update['email'] = $email;
			unset($values['email']);
		}
		if (array_key_exists('group', $values))
		{
			if (is_numeric($values['group']))
			{
				$update['group'] = (int) $values['group'];
			}
			unset($values['group']);
		}
		if ( ! empty($values))
		{
			$profile_fields = @unserialize($current_values->get('profile_fields')) ?: array();
			foreach ($values as $key => $val)
			{
				if ($val === null)
				{
					unset($profile_fields[$key]);
				}
				else
				{
					$profile_fields[$key] = $val;
				}
			}
			$update['profile_fields'] = serialize($profile_fields);
		}

		$update['updated_at'] = date('Y-m-d H:i:s');

		$affected_rows = \DB::update(\Config::get('simpleauth.table_name'))
			->set($update)
			->where('username', '=', $username)
			->execute(\Config::get('simpleauth.db_connection'));

		// Refresh user
		if ($this->user['username'] == $username)
		{
			$this->user = \DB::select_array(\Config::get('simpleauth.table_columns', array('*')))
				->where('username', '=', $username)
				->from(\Config::get('simpleauth.table_name'))
				->execute(\Config::get('simpleauth.db_connection'))->current();
		}

		return $affected_rows > 0;
	}

	public function create_login_hash()
	{
		if (empty($this->user))
		{
			throw new \SimpleUserUpdateException('User not logged in, can\'t create login hash.', 10);
		}

		$last_login = date('Y-m-d H:i:s');
		$login_hash = sha1(\Config::get('simpleauth.login_hash_salt').$this->user['username'].$last_login);

		\DB::update(\Config::get('simpleauth.table_name'))
			->set(array('last_login' => $last_login, 'login_hash' => $login_hash))
			->where('username', '=', $this->user['username'])
			->execute(\Config::get('simpleauth.db_connection'));

		$this->user['login_hash'] = $login_hash;

		return $login_hash;
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

		$username = \Session::get('username');
		$password = trim($password) ? trim($password) : trim(\Input::post(\Config::get('simpleauth.password_post_key', 'password')));
		if (empty($username) || empty($password)) return false;

		return (bool)\DB::select_array(\Config::get('simpleauth.table_columns', array('*')))
			->where('username', '=', $username)
			->and_where('password', '=', $this->hash_password($password))
			->from(\Config::get('simpleauth.table_name'))
			->execute(\Config::get('simpleauth.db_connection'))->current();
	}
}
