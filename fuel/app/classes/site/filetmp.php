<?php

class Site_FileTmp
{
	public static function get_file_tmps_uploaded($member_id, $is_throw_exception_not_exists_file = true, $is_delete_record_not_exists_file = false)
	{
		if (!$file_tmps_posted = Input::post('file_tmps')) throw new\HttpInvalidInputException('File not selected.');
		if (!$file_tmp_ids = Util_Array::cast_values(array_keys($file_tmps_posted), 'int', true))
		{
			throw new HttpInvalidInputException('Invalid input data.');
		}

		$file_tmps = \Util_db::get4ids('file_tmp', $file_tmp_ids);
		foreach ($file_tmps as $key => $file_tmp)
		{
			if ($file_tmp->member_id != $member_id) throw new \HttpForbiddenException;
			if ($file_tmps_posted[$file_tmp->id] != $file_tmp->name) throw new \HttpInvalidInputException('Invalid input data.');

			$file_tmps_description_posted = Input::post('file_tmps_description');
			if (!is_null($file_tmps_description_posted[$file_tmp->id]))
			{
				$file_tmps[$key]->description = trim($file_tmps_description_posted[$file_tmp->id]);
			}

			if (!file_exists(Config::get('site.upload.types.img.tmp.raw_file_path').$file_tmp->path.$file_tmp->name))
			{
				if ($is_throw_exception_not_exists_file) throw new\HttpInvalidInputException('File not exists.');
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
			if ($current_filesize > $accepted_filesize)  throw new \HttpInvalidInputException('Upload beyond the allowed size.');
		}

		return true;
	}
}
