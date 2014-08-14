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
	 * php oil r createuser email password name
	 *
	 * @return string
	 */
	public static function run($email, $password, $name)
	{
		try
		{
			if (!\Auth::create_user($email, $password, $name))
			{
				throw new \FuelException('Failed to create user.');
			}

			return 'Create site user '.$name.'.';
		}
		catch(Exception $e)
		{
			return 'admin::createuser error: '.$e->getMessage();
		}
	}
}

/* End of file tasks/createuser.php */
