<?php
class Util_Toolkit
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
			if (isset($data[$item]) && preg_match('/[\r\n]/u', $data[$item]) === 1)
			{
				throw new EmailValidationFailedException('One or more email headers did not pass validation: '.$item);
			}
		}

		$email = Email::forge();
		$email->from($data['from_address'], isset($data['from_name']) ? $data['from_name'] : null);
		$email->to($data['to_address'], isset($data['to_name']) ? $data['to_name'] : null);
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

	public static function get_neighboring_value_in_array($list, $target)
	{
		$list = (array)$list;

		$hit    = null;
		$before = null;
		$after  = null;
		foreach ($list as $value)
		{
			if ($value == $target)
			{
				$hit = $value;
				continue;
			}
			if (isset($hit))
			{
				$after = $value;
				break;
			}
			$before = $value;
		}

		return array($before, $after);
	}

	public static function get_past_time($time, $unit = 3600, $base_time = '')
	{
		if (!$base_time) $base_time = time();

		if ($base_time < $time) return false;
		if (!$unit) return false;

		return ceil(($base_time - $time) / $unit);
	}

	public static function reverse_bool($bool, $is_int = false)
	{
		if ($is_int)
		{
			if (empty($bool)) return 1;

			return 0;
		}

	 return !$bool;
	}

	public static function convert_to_attr($attrs, $default_attrs = array())
	{
		$attrs = array_merge_recursive($default_attrs, $attrs);

		return Util_Array::conv_arrays2str($attrs);
	}

	public static function shell_exec($command)
	{
		return shell_exec("{$command} 2>&1");
	}

	/**
	 * Executes command and returns the output.
	 *
	 * @param   string   $program   The name of the executable.
	 * @param   string   $params    The parameters of the executable.
	 * @param   boolean  $passthru  Returns the output if false or pass it to browser.
	 * @return  mixed    Either returns the output or returns nothing.
	 */
	public static function exec_command($command, $params, $passthru = false, $throw_exception = true)
	{
		$code = 0;
		$output = null;

		$command .= " ".$params;
		$passthru ? passthru($command) : exec($command, $output, $code);

		if ($throw_exception && $code != 0)
		{
			throw new \FuelException("command failed. Returned with $code.<br /><br />Command:\n <code>$command</code>");
		}

		return $output;
	}

	public static function log_error($message, $level = 'error')
	{
		if (!FBD_OUTPUT_ERROR_LOG_LEVEL) return;
		if (!in_array($level, array('error', 'warning', 'info', 'debug'))) throw new InvalidArgumentException('Second parameter is invalid.');
		switch (FBD_OUTPUT_ERROR_LOG_LEVEL)
		{
			case 'error':
				if (in_array($level, array('warning', 'info', 'debug'))) return;
				break;
			case 'warning':
				if (in_array($level, array('info', 'debug'))) return;
				break;
			case 'info':
				if ($level == 'debug') return;
				break;
			case 'debug':
			default :
				break;
		}

		\Log::$level(
			$message.': '.
			\Input::uri().' '.
			\Input::ip().
			' "'.\Input::user_agent().'"'
		);
	}
}
