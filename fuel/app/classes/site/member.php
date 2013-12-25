<?php

class Site_Member
{
	public static function save_profile_image(Model_Member $member, $file_path = null)
	{
		if (Config::get('site.upload.types.img.types.m.save_as_album_image'))
		{
			$album_id = \Album\Model_Album::get_id_for_foreign_table($member->id, 'member');
			list($album_image, $file) = \Album\Model_AlbumImage::save_with_file($album_id, $member, PRJ_PUBLIC_FLAG_ALL, $file_path);

			$member->file_id = $album_image->file->id;
			$member->save();

			$type_key = 'album_image_profile';
			$foreign_id = $album_image->id;
		}
		else
		{
			$old_file_id = $member->file_id;
			$options = Site_Upload::get_uploader_options($member->id);
			$uploadhandler = new Site_Uploader($options);
			$file = $uploadhandler->save();
			if (!empty($file->error)) throw new FuelException($file->error);

			$member->file_id = $file->id;
			$member->save();

			// 古いファイルの削除
			$deleted_filesize = (int)Model_File::delete_with_timeline($old_file_id);

			$type_key = 'profile_image';
			$foreign_id = $file->id;
		}
		// timeline 投稿
		\Timeline\Site_Model::save_timeline($member->id, PRJ_PUBLIC_FLAG_ALL, $type_key, $foreign_id);

		return $file;
	}
}
