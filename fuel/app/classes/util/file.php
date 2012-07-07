<?php
class Util_file
{
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
}
