<?php
class Util_file
{
	public static function move($original_file, $moved_file)
	{
		if (!file_exists($original_file))
		{
			throw new FuelException('Original file not exists.');
		}

		return rename($original_file, $moved_file);
	}

	public static function copy($original_file, $moved_file)
	{
		if (!file_exists($original_file))
		{
			throw new FuelException('Original file not exists.');
		}

		return copy($original_file, $moved_file);
	}

	/**
	 * type:
	 *   relative: 縦横比を維持
	 *   absolute: 指定の長さに変更
	 *   crop    : 指定の長さになるようにトリミング
	 */
	public static function resize($original_file, $resized_file, $width, $height, $type = 'relative')
	{
		$image = Image::load($original_file);
		switch ($type)
		{
			case 'relative':
				$image->resize($width, $height);
				break;
			case 'absolute':
				$image->resize($width, $height, false);
				break;
			case 'crop':
				$image->crop_resize($width, $height);
				break;
		}

		return $image->save($resized_file);
	}

	public static function correct_orientation($original_file, $exif_orientation_info, $corrected_file = null)
	{
		if (!file_exists($original_file))
		{
			throw new FuelException('Original file not exists.');
		}
		if (!$corrected_file) $corrected_file = $original_file;

		$degrees   = 0;
		$direction = null;
		switch ((int)$exif_orientation_info)
		{
			case 2:
				$direction = 'vertical';
				break;
				break;
			case 3:
				$degrees = 180;
				break;
			case 4:
				$direction = 'horizontal';
				break;
			case 5:
				$degrees = 270;
				$direction = 'vertical';
				break;
			case 6:
				$degrees = 90;
				break;
			case 7:
				$degrees = 90;
				$direction = 'vertical';
				break;
			case 8:
				$degrees = 270;
				break;
		}
		if (!$degrees && !$direction) return;

		$image = Image::load($original_file);
		if ($degrees)   $image->rotate($degrees);
		if ($direction) $image->flip($direction);

		return $image->save($corrected_file);
	}

	/**
	 * use for remove exif.
	 */
	public static function resave($file_path)
	{
		return Image::load($file_path)->save($file_path);
	}

	public static function remove($file)
	{
		if (!file_exists($file)) return;
		if (!$return = unlink($file))
		{
			throw new FuelException('Remove file error: '.$file);
		}

		return $return;
	}

	public static function check_extension($file_path, $arrow_extentions = array('jpeg', 'jpg', 'png', 'gif'))
	{
		if (!$extension = strtolower(self::get_extension($file_path))) return false;

		if (in_array($extension, $arrow_extentions)) return $extension;

		return false;
	}

	public static function get_extension($file_path, $arrow_extentions = array('jpeg', 'jpg', 'png', 'gif'))
	{
		if (file_exists($file_path)) return false;
		$info = pathinfo($file_path);

		return isset($info['extension']) ? $info['extension'] : false;
	}

	public static function get_extension_from_filename($filename)
	{
		return substr($filename, strrpos($filename, '.') + 1);
	}

	public static function get_image_type($file_path)
	{
		$imginfo = getimagesize($file_path);
		$type = $imginfo[2];
		switch ($type)
		{
			case IMAGETYPE_JPEG:
				return 'jpg';
			case IMAGETYPE_GIF:
				return 'gif';
			case IMAGETYPE_PNG:
				return 'png';
		}

		return false;
	}

	public static function check_file_type($file_path, $arrow_extentions = array(), $format = '', $upload_type = 'img')
	{
		if (empty($arrow_extentions)) $arrow_extentions = Site_Upload::get_accept_format($upload_type);

		if ($format)
		{
			if (!$extension = Site_Upload::check_file_format_is_accepted($format, $upload_type)) return false;
		}
		else
		{
			if (!$extension = self::check_extension($file_path, $arrow_extentions)) return false;
		}
		if ($upload_type == 'file') return $extension;

		$imginfo = getimagesize($file_path);
		$type = $imginfo[2];
		switch ($type)
		{
			case IMAGETYPE_JPEG:
				if ($extension == 'jpg' || $extension == 'jpeg') return 'jpg';
				break;
			case IMAGETYPE_GIF:
				if ($extension == 'gif') return 'gif';
				break;
			case IMAGETYPE_PNG:
				if ($extension == 'png') return 'png';
				break;
		}

		return false;
	}

	public static function make_filename($original_filename, $extension = '', $prefix = '', $with_extension = true, $delimitter = '_', $is_seccure = false)
	{
		$filename = Util_string::get_random($original_filename, $extension, $is_seccure);
		if ($prefix) $prefix .= $delimitter;
		if ($with_extension) $filename .= '.'.$extension;

		return $prefix.$filename;
	}

	public static function make_dir($path, $mode = 0777)
	{
		if (file_exists($path))
		{
			throw new Exception('target directory is already exists.');
		}
		if (!mkdir($path, $mode))
		{
			throw new Exception('mkdir error.');
		}
		if (!chmod($path, $mode))
		{
			throw new Exception('chmod error.');
		}

		return true;
	}

	public static function make_dir_recursive($path, $mode = 0777)
	{
		if (file_exists($path)) return true;

		return mkdir($path, $mode, true);
	}

	public static function check_exists_file_path($path, $check_level = 0)
	{
		$dirs = explode('/', $path);
		$max  = count($dirs);
		if (!$check_level || $check_level > $max) $check_level = $max;

		for ($i = 1; $i <= $check_level; $i++)
		{
			$check_path = implode('/', $dirs);
			if (file_exists($check_path)) return $check_path;
			array_pop($dirs);
		}

		return false;
	}

	public static function chmod_recursive($path, $mode = 0777, $is_directory_only = true)
	{
		$d = dir($path);
		$base_path = $path;
		while (false !== ($entry = $d->read()))
		{
			if ($entry == '.' || $entry == '..') continue;
			if ($is_directory_only && !is_dir($entry)) continue;

			$path = sprintf('%s/%s', $base_path, $entry);
			chmod($path, $mode);

			if (is_dir($path))
			{
				self::chmod_recursive($path, $mode);
			}
		}
	}

	public static function get_content_type_string($extention)
	{
		switch ($extention) {
			case 'jpeg':
			case 'jpg':
				return 'image/jpeg';
				break;
			case 'gif':
				return 'image/gif';
				break;
			case 'png':
				return 'image/png';
				break;
			default:
				return false;
		}
	}

	public static function get_file_recursive($path, $is_name_only = false, $is_get_all_files = false)
	{
		$files = array();
		$d = dir($path);
		$base_path = $path;
		while (false !== ($entry = $d->read()))
		{
			if ($entry == '.' || $entry == '..') continue;
			if (!$is_get_all_files && static::check_name_dot_started($entry)) continue;
			$path = sprintf('%s/%s', $base_path, $entry);
			if (is_dir($path))
			{
				$files = array_merge($files, self::get_file_recursive($path));
			}
			else
			{
				$files[] = $is_name_only ? $entry : $path;
			}
		}

		return $files;
	}

	public static function get_path_partial($path, $length = 1, $offset = 0)
	{
		$parts = explode('/', $path);
		$parts = array_reverse($parts);
		$return = '';
		for ($i = 0; $i < $length; $i++)
		{
			if ($i < $offset) continue;

			if (strlen($return) == 0)
			{
				$return = $parts[$i];
				continue;
			}

			$return = $parts[$i].'/'.$return;
		}

		return $return;
	}

	public static function check_name_dot_started($filename)
	{
		return substr($filename, 0, 1) == '.';
	}
}
