<?php

class Site_Member
{
	public static function save_profile_image(Model_Member $member, $file_path = null)
	{
		if (Module::loaded('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('file', $member->file_id);

		if (Module::loaded('album') && Config::get('site.upload.types.img.types.m.save_as_album_image'))
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
			if ($member->file_id && $file_old = Model_File::find($member->file_id))
			{
				$file_old->delete();
			}
			$options = Site_Upload::get_uploader_options($member->id);
			$uploadhandler = new Site_Uploader($options);
			$file = $uploadhandler->save();
			if (!empty($file->error)) throw new FuelException($file->error);

			$member->file_id = $file->id;
			$member->save();

			$type_key = 'profile_image';
			$foreign_id = $file->id;
		}
		// timeline 投稿
		if (Module::loaded('timeline')) \Timeline\Site_Model::save_timeline($member->id, PRJ_PUBLIC_FLAG_ALL, $type_key, $foreign_id);

		return $file;
	}
}
