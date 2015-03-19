<?php

class Site_FileTmp
{
	public static function get_file_tmps_and_check_filesize($member_id = null, $filesize_total = null, $type = 'img')
	{
		$file_tmps = self::get_file_tmps_uploaded($member_id, false, $type);
		if ($member_id) self::check_uploaded_under_accepted_filesize($file_tmps, $filesize_total, Site_Upload::get_accepted_filesize($member_id));

		return $file_tmps;
	}

	public static function get_file_tmps_uploaded($member_id = null, $check_selected = false, $type = 'img', $check_file_exists = true, $is_delete_not_exists_file = false)
	{
		$file_tmps = array();
		$post_key = ($type == 'img') ? 'image_tmp' : 'file_tmp';
		if (!$file_tmps_posted = Input::post($post_key))
		{
			if ($check_selected) throw new HttpInvalidInputException('File not selected.');

			return array();
		}

		if (!$file_tmp_ids = Util_Array::cast_values(array_keys($file_tmps_posted), 'int', true))
		{
			throw new HttpInvalidInputException('Invalid input data.');
		}
		if (!$file_tmps = Model_FileTmp::get4ids($file_tmp_ids))
		{
			throw new FuelException('ファイルが選択されていません。');
		}

		foreach ($file_tmps as $key => $file_tmp)
		{
			if ($member_id && $file_tmp->member_id != $member_id) throw new HttpForbiddenException;
			if ($file_tmps_posted[$file_tmp->id] != $file_tmp->name) throw new HttpInvalidInputException('Invalid input data.');

			$file_tmps_description_posted = Input::post($post_key.'_description');
			if (isset($file_tmps_description_posted[$file_tmp->id]))
			{
				$file_tmps[$key]->description = trim($file_tmps_description_posted[$file_tmp->id]);
			}

			$file_tmp_path = Site_Upload::get_uploaded_file_path($file_tmp->name, 'raw', 'img', true);
			if (conf('upload.storageType') == 'normal' && !file_exists($file_tmp_path))
			{
				if ($check_file_exists) throw new HttpInvalidInputException('File not exists.');

				if ($is_delete_not_exists_file) $file_tmp->delete();
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

	public static function save_images($file_tmps, $parent_id, $parent_id_field, $related_table, $public_flag = null, $is_ignore_member_id = false, $type = 'img')
	{
		if (!in_array($type, array('img', 'file'))) throw new InvalidArgumentException('Parameter type is invalid.');

		$moved_files       = array();
		$related_table_ids = array();
		if (!$file_tmps) return array($moved_files, $related_table_ids);

		$model = Site_Model::get_model_name($related_table);
		$related_table_obj = $model::forge();
		if (!$file_cate = $related_table_obj->get_image_prefix())
		{
			throw new \InvalidArgumentException('Parameter $related_table is invalid.');
		}

		$new_filepath_prefix = Site_Upload::get_filepath_prefix($file_cate, $parent_id);
		$new_filename_prefix = Site_Upload::convert_filepath2filename($new_filepath_prefix);
		$is_save_storage = conf('upload.storageType') != 'normal';
		if (!$is_save_storage)
		{
			$new_file_dir = Site_Upload::get_uploaded_path('raw', 'img', true, false, $new_filepath_prefix);
			if (!Site_Upload::check_and_make_uploaded_dir($new_file_dir, conf('upload.check_and_make_dir_level'), conf('upload.mkdir_mode')))
			{
				throw newFuelException('Failed to make save dirs.');
			}
		}
		foreach ($file_tmps as $id => $file_tmp)
		{
			$old_file_path = Site_Upload::get_uploaded_file_path($file_tmp->name, 'raw', 'img', true);
			$moved_files[$file_tmp->id] = array('from' => $old_file_path);
			if (!$is_save_storage)
			{
				$new_file_path = $new_file_dir.$file_tmp->name;
				Util_file::move($old_file_path, $new_file_path);
				$moved_files[$file_tmp->id]['to'] = $new_file_path;
				$moved_files[$file_tmp->id]['filepath_prefix'] = $new_filepath_prefix;
			}
			if ($type == 'img')
			{
				$moved_files[$file_tmp->id]['from_thumbnail'] = Site_Upload::get_uploaded_file_path($file_tmp->name, 'thumbnail', 'img', true);
			}
			$file = Model_File::move_from_file_tmp($file_tmp, $new_filename_prefix, $is_ignore_member_id, $type);

			$related_table_obj = $model::forge();
			$related_table_obj->{$parent_id_field} = $parent_id;
			$related_table_obj->file_name = $file->name;
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
			if (!empty($moved_file['to']) && !empty($moved_file['filepath_prefix']))
			{
				Site_Upload::make_thumbnails($moved_file['to'], $moved_file['filepath_prefix'], true, $additional_sizes_key);
			}
			if (!empty($moved_file['from']) && file_exists($moved_file['from'])) Util_file::remove($moved_file['from']);
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

	public static function get_file_objects($file_tmps, $member_id, $is_admin = false, $type = 'img')
	{
		$options = Site_Upload::get_upload_handler_options($member_id, $is_admin, true, null, 0, true, $type);
		$uploadhandler = new MyUploadHandler($options, false);

		return $uploadhandler->get_file_objects_from_file_tmps($file_tmps, $type);
	}
}
