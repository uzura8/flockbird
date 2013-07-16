<?php
namespace Album;

class Site_Util
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
		if (!$cover_album_image_id || !$album_image = Model_AlbumImage::find($cover_album_image_id, array('related'  => 'file')))
		{
			$album_image = Model_AlbumImage::find('first',
				array(
					'where'    => array('album_id' => $album_id),
					'related'  => 'file',
					'order_by' => array('created_at' => 'asc'),
					'rows_limit' => 1,
				)
			);
		}

		return (!empty($album_image)) ? $album_image->get_image() : 'ai';
	}

	public static function get_neighboring_album_image_ids($album_id, $album_image_id, $sort = 'id')
	{
		$album_image = Model_AlbumImage::query()->select('id')->where('album_id', $album_id)->order_by($sort)->where('id', '<', $album_image_id)->get_one();	
		$prev_id = (isset($album_image->id))? $album_image->id : null;
		$album_image = Model_AlbumImage::query()->select('id')->where('album_id', $album_id)->order_by($sort)->where('id', '>', $album_image_id)->get_one();	
		$next_id = (isset($album_image->id))? $album_image->id : null;

		return array($prev_id, $next_id);
	}

	public static function get_album_image_page_title($album_image_name, $original_filename)
	{
		$title = sprintf(\Config::get('term.album_image'), \Config::get('term.album_image'));
		if ($album_image_name)
		{
			$title = $album_image_name;
		}
		elseif ($original_filename)
		{
			$title = $original_filename;
		}

		return $title;
	}
}
