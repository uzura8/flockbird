<?php

class Site_FileTmp
{
	public static function get_file_tmps_uploaded($member_id, $is_check_select_files = false, $is_throw_exception_not_exists_file = true, $is_delete_record_not_exists_file = false)
	{
		$file_tmps = array();
		if (!$file_tmps_posted = Input::post('file_tmps'))
		{
			if ($is_check_select_files)  throw new HttpInvalidInputException('File not selected.');

			return $file_tmps;
		}
		if (!$file_tmp_ids = Util_Array::cast_values(array_keys($file_tmps_posted), 'int', true))
		{
			throw new HttpInvalidInputException('Invalid input data.');
		}
		if (!$file_tmps = Util_db::get4ids('file_tmp', $file_tmp_ids))
		{
			throw new FuelException('ファイルが選択されていません。');
		}

		foreach ($file_tmps as $key => $file_tmp)
		{
			if ($file_tmp->member_id != $member_id) throw new HttpForbiddenException;
			if ($file_tmps_posted[$file_tmp->id] != $file_tmp->name) throw new HttpInvalidInputException('Invalid input data.');

			$file_tmps_description_posted = Input::post('file_tmps_description');
			if (!is_null($file_tmps_description_posted[$file_tmp->id]))
			{
				$file_tmps[$key]->description = trim($file_tmps_description_posted[$file_tmp->id]);
			}

			if (!file_exists(Config::get('site.upload.types.img.tmp.raw_file_path').$file_tmp->path.$file_tmp->name))
			{
				if ($is_throw_exception_not_exists_file) throw new HttpInvalidInputException('File not exists.');
				if ($is_delete_record_not_exists_file) $file_tmp->delete();
				unset($file_tmps[$file_tmp->id]);
			}
		}

		return $file_tmps;
	}

	public static function check_uploaded_under_accepted_filesize($file_tmps, $current_filesize, $accepted_filesize)
	{
		foreach ($file_tmps as $file_tmp)
		{
			$current_filesize += $file_tmp->filesize;
			if ($current_filesize > $accepted_filesize)  throw new HttpInvalidInputException('Upload beyond the allowed size.');
		}

		return true;
	}

	public static function save_as_album_images($file_tmps, $album_id, $public_flag)
	{
		$moved_files     = array();
		$album_image_ids = array();
		if (!$file_tmps) return array($moved_files, $album_image_ids);

		$new_filepath = Site_Upload::get_filepath('ai', $album_id);
		$new_file_dir  = Config::get('site.upload.types.img.raw_file_path').$new_filepath;
		if (!Site_Upload::check_and_make_uploaded_dir($new_file_dir, Config::get('site.upload.check_and_make_dir_level'), Config::get('site.upload.mkdir_mode')))
		{
			throw newFuelException('Failed to make save dirs.');
		}

		foreach ($file_tmps as $file_tmp)
		{
			$old_file_path = Config::get('site.upload.types.img.tmp.raw_file_path').$file_tmp->path.$file_tmp->name;
			$thumbnail_file_path = Config::get('site.upload.types.img.tmp.raw_file_path').$file_tmp->path.'thumbnail/'.$file_tmp->name;
			$new_file_path = $new_file_dir.$file_tmp->name;
			Util_file::move($old_file_path, $new_file_path);
			$file = Model_File::move_from_file_tmp($file_tmp, $new_filepath);

			$album_image = \Album\Model_AlbumImage::forge();
			$album_image->album_id    = $album_id;
			$album_image->file_id     = $file->id;
			$album_image->name        = $file_tmp->description;
			$album_image->public_flag = $public_flag;
			$album_image->shot_at     = !empty($file->shot_at) ? $file->shot_at : date('Y-m-d H:i:s');
			$album_image->save();

			$moved_files[$file_tmp->id] = array(
				'from' => $old_file_path,
				'to'   => $new_file_path,
				'from_thumbnail' => $thumbnail_file_path,
				'filepath' => $new_filepath,
			);
			$album_image_ids[] = $album_image->id;
		}

		return array($moved_files, $album_image_ids);
	}

	public static function make_and_remove_thumbnails($moved_files)
	{
		foreach ($moved_files as $moved_file)
		{
			Site_Upload::make_thumbnails($moved_file['to'], $moved_file['filepath']);
			Util_file::remove($moved_file['from_thumbnail']);
		}
	}

	public static function move_files_to_tmp_dir($moved_files)
	{
		foreach ($moved_files as $moved_file)
		{
			Util_file::move($moved_file['to'], $moved_file['from']);
		}
	}

	public static function get_file_objects($file_tmps, $member_id)
	{
		$options = Site_Upload::get_upload_handler_options($member_id);
		$uploadhandler = new MyUploadHandler($options, false);

		return $uploadhandler->get_file_objects_from_file_names($file_tmps);
	}
}
