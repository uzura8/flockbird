<?php
namespace Album;

class Site_Test
{
	public static function setup_album_image($member_id, $album_image_values = null, $create_count = 1, $album_id = null)
	{
		$public_flag = isset($album_image_values['public_flag']) ? $album_image_values['public_flag'] : conf('public_flag.default');
		if (!$album_id)
		{
			$album_values = array(
				'name' => 'test album_image.',
				'body' => 'This is test for album_image.',
				'public_flag' => $public_flag,
			);
			$album = static::force_save_album($member_id, $album_values);
			$album_id = $album->id;
		}
		if (!$album_image_values)
		{
			$album_image_values = array(
				'name' => 'test',
				'public_flag' => conf('public_flag.default'),
			);
		}
		for ($i = 0; $i < $create_count; $i++)
		{
			$upload_file_path = static::setup_upload_file();
			list($album_image, $file) = Model_AlbumImage::save_with_relations($album_id, null, null, $upload_file_path, 'album', $album_image_values);
		}

		return $album_image;
	}

	public static function force_save_album($member_id, $values, Model_Album $album = null)
	{
		// album save
		if (!$album) $album = Model_Album::forge();
		$album->name = $values['name'];
		$album->body = $values['body'];
		$album->public_flag = $values['public_flag'];
		$album->member_id = $member_id;
		$album->save();
		if (\Module::loaded('timeline'))
		{
			\Timeline\Site_Model::save_timeline($member_id, $values['public_flag'], 'album', $album->id, $album->updated_at);
		}

		return $album;
	}

	public static function setup_upload_file()
	{
		// prepare upload file.
		$original_file = FBD_BASEPATH.'data/development/test/media/img/sample_01.jpg';
		$upload_file = APPPATH.'tmp/sample.jpg';
		\Util_file::copy($original_file, $upload_file);
		chmod($upload_file, 0777);

		return $upload_file;
	}
}
