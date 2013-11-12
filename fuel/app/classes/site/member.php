<?php

class Site_Member
{
	public static function save_profile_image(Model_Member $member, $file_path = null, $is_save_original_filename = true)
	{
		if (Config::get('site.upload.types.img.types.m.save_as_album_image'))
		{
			$album_id = \Album\Model_Album::get_id_for_foreign_table($member->id, 'member');
			$sizes = Arr::merge(Config::get('site.upload.types.img.types.ai.additional_sizes.profile'), \Config::get('site.upload.types.img.types.ai.sizes'));
			$album_image = \Album\Model_AlbumImage::save_with_file($album_id, $member, Config::get('site.public_flag.default'), null, null, $sizes, $file_path, $is_save_original_filename);
			$member->file_id = $album_image->file->id;
			$member->save();
			$foreign_table = 'album_image';
			$foreign_id = $album_image->id;
		}
		else
		{
			// 古いファイルの削除
			$deleted_filesize = (int)Model_File::delete_with_file($member->file_id);
			Model_Member::add_filesize($member->id, -$deleted_filesize);

			$file = Site_Upload::upload('m', $member->id, $member->id, $member->filesize_total, array(), $member->get_image(), $member->file_id, $file_path, $is_save_original_filename);
			$member->file_id = $file->id;
			$member->save();
			Model_Member::add_filesize($member->id, $file->filesize);
			$foreign_table = 'file';
			$foreign_id = $file->id;
		}
		// timeline 投稿
		\Timeline\Site_Model::save_timeline($member->id, null, 'profile_image', $foreign_id, null, null, $foreign_table);
	}
}
