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

	public static function check_album_disabled_to_update($album_foreign_table, $is_bool = false)
	{
		if ($album_foreign_table && in_array($album_foreign_table, self::get_album_foreign_tables()))
		{
			if ($is_bool) return true;

			switch ($album_foreign_table)
			{
				case 'note':
					$message_prefix = sprintf('%s用%sの', \Config::get('term.note'), \Config::get('term.album'));
					break;
				case 'member':
					$message_prefix = sprintf('%s用%sの', \Config::get('term.profile'), \Config::get('term.album'));
					break;
				default :
					$message_prefix = '';
					break;
			}
			return array('message' => sprintf('%s%sは変更できません。', $message_prefix, \Config::get('term.public_flag.label')));
		}

		return false;
	}

	public static function get_album_foreign_tables()
	{
		return array('note', 'member');
	}

	public static function get_file_objects($album_images, $member_id, $album_id)
	{
		$options = \Site_Upload::get_upload_handler_options($member_id, false, 'ai', $album_id);
		$uploadhandler = new \MyUploadHandler($options, false);
		$album_image_names_posted = \Input::post('file_description');

		return $uploadhandler->get_file_objects_from_album_images($album_images, $album_image_names_posted);
	}

	public static function update_album_images4file_objects($album_images, $files, $public_flag = null)
	{
		foreach ($files as $file)
		{
			if(empty($album_images[$file->id])) continue;
			$album_image = $album_images[$file->id];

			if ($album_image->name !== $file->description)
			{
				$album_image->name = $file->description;
			}
			if (!is_null($public_flag) && $album_image->public_flag != $public_flag)
			{
				$album_image->public_flag = $public_flag;
			}

			$album_image->save();
		}
	}
}
