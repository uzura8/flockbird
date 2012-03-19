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
}
