<?php
class Util_file
{
	public static function move($original_file, $moved_file)
	{
		return rename($original_file, $moved_file);
	}

	public static function resize($original_file, $resized_file, $width, $height)
	{
		return Image::load($original_file)
				->crop_resize($width, $height)
				->save($resized_file);
	}

	public static function remove($file)
	{
		if (!file_exists($file)) return;
		if (!$return = unlink($file))
		{
			throw new Exception('Remove image error.');
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

		return $info['extension'];
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

	public static function check_image_type($file_path, $arrow_extentions = array('jpeg', 'jpg', 'png', 'gif'), $type = '')
	{
		if ($type)
		{
			if (!preg_match('#^image/(jpeg|gif|ping)$#i', $type, $matches)) return false;
			switch ($matches[1])
			{
				case 'jpeg':
					$extension = 'jpg';
					break;
				case 'gif':
					$extension = 'gif';
					break;
				case 'png':
					$extension = 'png';
					break;
				default :
					return false;
			}
		}
		else
		{
			if (!$extension = self::check_extension($file_path, $arrow_extentions)) return false;
		}

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

	public static function make_thumbnails($original_file_dir, $original_file_name, $sizes, $skip_size_types = array(), $old_filename = '')
	{
		if ($skip_size_types && !is_array($skip_size_types)) $skip_size_types = (array)$skip_size_types;

		$original_file = $original_file_dir.'/'.$original_file_name;
		try
		{
			foreach ($sizes as $key => $config)
			{
				if (in_array($key, $skip_size_types)) continue;

				if (empty($config['width']) || empty($config['height']))
				{
					self::move($original_file, $config['path'].'/'.$original_file_name);
				}
				else
				{
					self::resize($original_file, $config['path'].'/'.$original_file_name, $config['width'], $config['height']);
					if ($old_filename) self::remove($config['path'].'/'.$old_filename);
				}
			}
			if ($old_filename) self::remove($original_file_dir.'/'.$old_filename);
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}

	public static function make_filename($original_filename, $extension = '', $prefix = '', $with_extension = true, $delimitter = '_')
	{
		$filename = Util_string::get_random($original_filename, $extension);
		if ($prefix) $prefix .= $delimitter;
		if ($with_extension) $filename .= '.'.$extension;

		return $prefix.$filename;
	}

	public static function make_dir($path)
	{
		if (file_exists($path))
		{
			throw new Exception('target directory is already exists.');
		}
		if (!mkdir($path, 0777))
		{
			throw new Exception('mkdir error.');
		}
		if (!chmod($path, 0777))
		{
			throw new Exception('chmod error.');
		}

		return true;
	}
}
