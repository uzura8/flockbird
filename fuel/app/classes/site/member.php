<?php

class Site_Member
{
	public static function save_profile_image(Model_Member $member, $file_path = null)
	{
		if (Module::loaded('timeline')) \Timeline\Model_Timeline::delete4foreign_table_and_foreign_ids('file', $member->file_id);

		if (Module::loaded('album') && conf('upload.types.img.types.m.save_as_album_image'))
		{
			$album_id = \Album\Model_Album::get_id_for_foreign_table($member->id, 'member');
			list($album_image, $file) = \Album\Model_AlbumImage::save_with_relations($album_id, $member, PRJ_PUBLIC_FLAG_ALL, $file_path, 'album_image_profile');

			$member->file_id = $album_image->file->id;
			$member->save();
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

			// timeline 投稿
			if (Module::loaded('timeline')) \Timeline\Site_Model::save_timeline($member->id, PRJ_PUBLIC_FLAG_ALL, 'profile_image', $file->id, $member->updated_at);
		}

		return $file;
	}

	public static function get_access_from($target_member_id, $self_member_id = 0)
	{
		if (!$self_member_id) return 'guest';
		if ($target_member_id == $self_member_id) return 'self';

		return 'member';
	}

	public static function get_access_from_member_relation($member_id, $self_member_id = 0)
	{
		if (!$self_member_id) return 'others';

		if ($member_id == $self_member_id) return 'self';
		if (Model_MemberRelation::check_relation('friend', $self_member_id, $member_id)) return 'friend';

		return 'member';
	}

	public static function get_config($member_id, $name)
	{
		$value = Model_MemberConfig::get_value($member_id, $name);
		if (!is_null($value)) return $value;

		return conf('member_config_default.'.$name);
	}
}
