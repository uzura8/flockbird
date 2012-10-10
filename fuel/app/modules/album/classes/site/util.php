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

	public static function get_album_cover_filename($cover_album_image_id = 0, $album_id = 0)
	{
		if ($cover_album_image_id)
		{
			$album_image = Model_AlbumImage::find($cover_album_image_id)->related('file')->get_one();
		}
		else
		{
			$album_image = Model_AlbumImage::find()->where('album_id', $album_id)->related('file')->order_by('created_at', 'asc')->get_one();
		}

		return (!empty($album_image->file->name)) ? $album_image->file->name : '';
	}
}
