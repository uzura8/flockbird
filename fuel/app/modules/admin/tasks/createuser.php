<?php
namespace Fuel\Tasks;

/**
 * Task CreateUser
 */

class CreateUser
{
	/**
	 * Usage (from command line):
	 *
	 * php oil r admin::createuser username password email group
	 *
	 * @return string
	 */
	public static function run($username, $password, $email, $group = 1)
	{
		try
		{
			if (!\Auth::create_user($username, $password, $email, $group))
			{
				throw new \FuelException('Failed to create user.');
			}

			return 'Create admin user '.$username.'.';
		}
		catch(Exception $e)
		{
			return 'admin::createuser error: '.$e->getMessage();
		}
	}
}

/* End of file tasks/createuser.php */
