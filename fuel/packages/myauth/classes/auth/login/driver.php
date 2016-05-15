<?php
namespace MyAuth;

class Auth_Login_Driver extends \Auth\Auth_Login_Driver
{
	/**
	 * Default password hash method
	 *
	 * @param   string
	 * @param   string
	 * @return  string
	 */
	public function hash_password($password, $username = '')
	{
		return base64_encode(hash_pbkdf2('sha256', $password, static::get_salt($username, \Config::get('auth.salt')), \Config::get('auth.iterations', 10000), 32, true));
	}

	/**
	 * Get salt different by users.
	 *
	 * @param   string
	 * @param   string
	 * @return  string
	 */
	public static function get_salt($username, $fixed_salt)
	{
		$salt = $username.pack('H*', hash('sha256', $fixed_salt));

		return $salt;
	}

	// ------------------------------------------------------------------------

	protected function perform_check() {}
	public function validate_user() {}
	public function login() {}
	public function logout() {}
	public function get_user_id() {}
	public function get_groups() {}
	public function get_email() {}
	public function get_screen_name() {}
}

/* end of file driver.php */
