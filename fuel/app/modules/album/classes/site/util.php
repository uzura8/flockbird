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

	public static function get_album_image_page_title($album_image_name, $original_filename)
	{
		$title = term('album_image');
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
		if (!$album_foreign_table) return false;
		if (!in_array($album_foreign_table, self::get_album_foreign_tables())) return false;

		if ($is_bool) return true;

		switch ($album_foreign_table)
		{
			case 'note':
				$message_prefix = sprintf('%s用%sの', term('note'), term('album'));
				break;
			case 'member':
				$message_prefix = sprintf('%s用%sの', term('profile'), term('album'));
				break;
			case 'timeline':
				$message_prefix = sprintf('%s用%sの', term('timeline'), term('album'));
				break;
			default :
				$message_prefix = '';
				break;
		}

		return array('message' => sprintf('%s%sは変更できません。', $message_prefix, term('public_flag.label')));
	}

	public static function get_album_foreign_tables()
	{
		return array('note', 'member', 'timeline');
	}

	public static function get_foreign_table_info($table_name)
	{
		$info = array('public_flag' => conf('public_flag.default'));
		switch ($table_name)
		{
			case 'note':
				$info['name'] = sprintf('%s用%s', term('note'), term('album'));
				break;
			case 'member':
				$info['name'] = sprintf('%s用%s', term('profile'), term('album'));
				$info['public_flag'] = PRJ_PUBLIC_FLAG_ALL;
				break;
			case 'timeline':
				$info['name'] = sprintf('%s用%s', term('timeline'), term('album'));
				break;
			default :
				break;
		}

		return $info;
	}

	public static function get_like_api_uri($album_image_id)
	{
		return sprintf('album/image/like/api/update/%d.json', $album_image_id);
	}

	public static function get_liked_member_api_uri($album_image_id)
	{
		return sprintf('album/image/like/api/member/%d.html', $album_image_id);
	}
}
