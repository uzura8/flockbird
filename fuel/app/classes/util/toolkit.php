<?php
class Util_toolkit
{
	public static function include_php_files($dir)
	{
		if (!is_dir($dir)) {
			return;
		}
		if ($dh = opendir($dir))
		{
			while (($file = readdir($dh)) !== false)
			{
				if ($file[0] === '.')
				{
					continue;
				}
				$path = realpath($dir . '/' . $file);
				if (is_dir($path))
				{
					self::util_include_php_files($path);
				}
				else
				{
					if (substr($file, -4, 4) === '.php')
					{
						include_once $path;
					}
				}
			}
			closedir($dh);
		}
	}

	public static function sendmail($data)
	{
		Package::load('email');

		$items = array('from_address', 'from_name', 'to_address', 'to_name', 'subject');
		foreach ($items as $item)
		{
			if (preg_match('/[\r\n]/u', $data[$item]) === 1)
			{
				throw new EmailValidationFailedException('One or more email headers did not pass validation: '.$item);
			}
		}

		$email = Email::forge();
		$email->from($data['from_address'], $data['from_name']);
		$email->to($data['to_address'], $data['to_name']);
		$email->subject($data['subject']);
		$email->body($data['body']);

		$email->send();
	}

	public static function convert_show_error($errors, $options = array())
	{
		$default = array(
			'open_list'    => \Config::get('validation.open_list', '<ul>'),
			'close_list'   => \Config::get('validation.close_list', '</ul>'),
			'open_error'   => \Config::get('validation.open_error', '<li>'),
			'close_error'  => \Config::get('validation.close_error', '</li>'),
			'no_errors'    => \Config::get('validation.no_errors', '')
		);
		$options = array_merge($default, $options);
		if (!is_array($errors)) $errors = (array)$errors;

		if (empty($errors))
		{
			return $options['no_errors'];
		}

		$output = $options['open_list'];
		foreach($errors as $e)
		{
			$output .= $options['open_error'].$e.$options['close_error'];
		}
		$output .= $options['close_list'];

		return $output;
	}
}
