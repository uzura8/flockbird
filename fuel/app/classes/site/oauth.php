<?php
class Site_Oauth
{
	public static function get_sex($response, $provider)
	{
		$sex = null;
		$sex_public_flag = PRJ_PUBLIC_FLAG_PRIVATE;
		switch ($provider)
		{
			case 'Facebook':
				if (!empty($response['auth']['raw']['gender']))
				{
					$sex = strtolower($response['auth']['raw']['gender']);
					$sex_public_flag = PRJ_PUBLIC_FLAG_MEMBER;
				}
				break;
			default :
				break;
		}

		return array($sex, $sex_public_flag);
	}

	public static function get_birthyear($response, $provider)
	{
		$birthyear = null;
		$birthyear_public_flag = PRJ_PUBLIC_FLAG_PRIVATE;

		return array($birthyear, $birthyear_public_flag);
	}

	public static function get_birthday($response, $provider)
	{
		$birthday = null;
		$birthday_public_flag = PRJ_PUBLIC_FLAG_PRIVATE;

		return array($birthday, $birthday_public_flag);
	}
}
