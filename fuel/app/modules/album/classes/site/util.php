<?php
namespace Album;

class Site_util
{
	public static function get_album_image_display_name($obj_album_image, $default = '')
	{
		if (!empty($obj_album_image->name))
		{
			return $obj_album_image->name;
		}

		if (!empty($obj_album_image->file) && !empty($obj_album_image->file->original_filename))
		{
			return $obj_album_image->file->original_filename;
		}

		return $default;
	}
}
