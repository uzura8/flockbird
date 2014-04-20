<?php

class Site_FileTmp
{
	public static function get_file_tmps_and_check_filesize($member_id = null, $filesize_total = null)
	{
		$file_tmps = self::get_file_tmps_uploaded($member_id);
		if ($member_id) self::check_uploaded_under_accepted_filesize($file_tmps, $filesize_total, Site_Upload::get_accepted_filesize($member_id));

		return $file_tmps;
	}

	public static function get_file_tmps_uploaded($member_id = null, $is_check_select_files = false, $is_throw_exception_not_exists_file = true, $is_delete_record_not_exists_file = false)
	{
		$file_tmps = array();
		if (!$file_tmps_posted = Input::post('file_tmp'))
		{
			if ($is_check_select_files)  throw new HttpInvalidInputException('File not selected.');

			return $file_tmps;
		}
		if (!$file_tmp_ids = Util_Array::cast_values(array_keys($file_tmps_posted), 'int', true))
		{
			throw new HttpInvalidInputException('Invalid input data.');
		}
		if (!$file_tmps = Site_Model::get4ids('file_tmp', $file_tmp_ids))
		{
			throw new FuelException('ファイルが選択されていません。');
		}

		foreach ($file_tmps as $key => $file_tmp)
		{
			if ($member_id && $file_tmp->member_id != $member_id) throw new HttpForbiddenException;
			if ($file_tmps_posted[$file_tmp->id] != $file_tmp->name) throw new HttpInvalidInputException('Invalid input data.');

			$file_tmps_description_posted = Input::post('file_tmp_description');
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

	public static function save_images($file_tmps, $parent_id, $parent_id_field, $related_table, $namespace = null, $public_flag = null, $is_ignore_member_id = false)
	{
		$moved_files     = array();
		$related_table_ids = array();
		if (!$file_tmps) return array($moved_files, $related_table_ids);

		$file_cate = Site_Upload::get_file_cate_from_table($related_table);
		if (!$file_cate) throw new \InvalidArgumentException('Parameter $related_table is invalid.');
		$new_filepath = Site_Upload::get_filepath($file_cate, $parent_id);
		$new_file_dir  = Config::get('site.upload.types.img.raw_file_path').$new_filepath;
		if (!Site_Upload::check_and_make_uploaded_dir($new_file_dir, Config::get('site.upload.check_and_make_dir_level'), Config::get('site.upload.mkdir_mode')))
		{
			throw newFuelException('Failed to make save dirs.');
		}

		foreach ($file_tmps as $id => $file_tmp)
		{
			$old_file_path = Config::get('site.upload.types.img.tmp.raw_file_path').$file_tmp->path.$file_tmp->name;
			$thumbnail_file_path = Config::get('site.upload.types.img.tmp.raw_file_path').$file_tmp->path.'thumbnail/'.$file_tmp->name;
			$new_file_path = $new_file_dir.$file_tmp->name;
			Util_file::move($old_file_path, $new_file_path);
			$moved_files[$file_tmp->id] = array(
				'from' => $old_file_path,
				'to'   => $new_file_path,
				'from_thumbnail' => $thumbnail_file_path,
				'filepath' => $new_filepath,
			);
			$file = Model_File::move_from_file_tmp($file_tmp, $new_filepath, $is_ignore_member_id);

			$model = Site_Model::get_model_name($related_table, $namespace);
			$related_table_obj = $model::forge();
			$related_table_obj->{$parent_id_field} = $parent_id;
			$related_table_obj->file_id = $file->id;
			$related_table_obj->name = $file_tmp->description;
			$related_table_obj->shot_at = !empty($file->shot_at) ? $file->shot_at : date('Y-m-d H:i:s');
			if (!is_null($public_flag)) $related_table_obj->public_flag = $public_flag;
			$related_table_obj->save();
			$related_table_ids[] = $related_table_obj->id;
		}

		return array($moved_files, $related_table_ids);
	}

	public static function make_and_remove_thumbnails($moved_files, $additional_sizes_key = null)
	{
		foreach ($moved_files as $moved_file)
		{
			Site_Upload::make_thumbnails($moved_file['to'], $moved_file['filepath'], true, $additional_sizes_key);
			if (!empty($moved_file['from_thumbnail'])) Util_file::remove($moved_file['from_thumbnail']);
		}
	}

	public static function move_files_to_tmp_dir($moved_files)
	{
		foreach ($moved_files as $moved_file)
		{
			Util_file::move($moved_file['to'], $moved_file['from']);
		}
	}

	public static function get_file_objects($file_tmps, $member_id, $is_admin = false)
	{
		$options = Site_Upload::get_upload_handler_options($member_id, $is_admin);
		$uploadhandler = new MyUploadHandler($options, false);

		return $uploadhandler->get_file_objects_from_file_tmps($file_tmps);
	}
}
