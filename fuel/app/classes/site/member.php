<?php

class Site_Member
{
	public static function save_profile_image($member_obj, $file_path = null, $is_save_original_filename = true)
	{
		if (Config::get('site.upload.types.img.types.m.save_as_album_image'))
		{
			$album_id = \Album\Model_Album::get_id_for_foreign_table($member_obj->id, 'member');
			$sizes = Arr::merge(Config::get('site.upload.types.img.types.ai.additional_sizes.profile'), \Config::get('site.upload.types.img.types.ai.sizes'));
			$album_image = \Album\Model_AlbumImage::save_with_file($album_id, $member_obj, Config::get('site.public_flag.default'), null, null, $sizes, $file_path, $is_save_original_filename);
			$member_obj->file_id = $album_image->file->id;
			$member_obj->save();
		}
		else
		{
			$file = Site_Upload::upload('m', $member_obj->id, $member_obj->id, $member_obj->filesize_total, array(), $member_obj->get_image(), $member_obj->file_id, $file_path, $is_save_original_filename);
			$member_obj->file_id = $file->id;
			$member_obj->save();
			Model_Member::add_filesize($member_obj->id, $file->filesize);
		}
	}
}
