<?php
namespace Album;

class Site_Model
{
	public static function move_from_tmp_to_album_image($album_id, $member_obj, $file_tmp, $public_flag, $is_update_member_filesize_total = false)
	{
		\Site_Upload::move_tmp_to_file($file_tmp, false);// 一時ファイルの移動(raw ファイルは残す)
		$file = \Model_File::save_from_file_tmp($file_tmp);// file の保存

		if ($is_update_member_filesize_total)
		{
			$member_obj->filesize_total += $size;
			$member_obj->save();
		}

		$album_image_name = \Model_FileTmpConfig::get_value_for_name($file_tmp->id, 'album_image_name');
		$album_image = \Album\Model_AlbumImage::save_with_file($album_id, $member_obj, $public_flag, $album_image_name, $file);// album_image の保存
		$file_path_name = $file_tmp->path.$file_tmp->name;
		$file_tmp->delete();
		\Util_file::remove(\Config::get('site.upload.types.img.tmp.raw_file_path').$file_path_name);// file_tmp の raw  ファイルを削除

		return $album_image;
	}
}
