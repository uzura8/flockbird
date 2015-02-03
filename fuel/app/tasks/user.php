<?php
namespace Fuel\Tasks;

/**
 * Task SetupDB
 */

class User
{
	//private static $absolute_execute = false;

	public function __construct($args = null)
	{
		//self::$absolute_execute = \Cli::option('absolute_execute', false);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r user:create email password name
	 *
	 * @return string
	 */
	public static function create($email, $password, $name)
	{
		try
		{
			if (!\Auth::create_user($email, $password, $name))
			{
				throw new \FuelException('Failed to create user.');
			}

			return \Util_Task::output_message('Create site user '.$name.'.');
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('createuser error: %s', $e->getMessage()), false);
		}
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r user:delete user_id
	 *
	 * @return string
	 */
	public static function delete($user_id)
	{
		try
		{
			\Site_Member::delete($user_id);

			return \Util_Task::output_message(sprintf('Delete site user. id: %d', $user_id));
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Delete site user error: %s', $e->getMessage()), false);
		}
	}
}

/* End of file tasks/user.php */
