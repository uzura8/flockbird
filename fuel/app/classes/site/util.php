<?php
class Site_util
{
	public static function check_is_admin_request()
	{
		if (Module::loaded('admin') && Request::main()->route->module == 'admin')
		{
			return true;
		}

		return false;
	}

	public static function get_middle_dir($id)
	{
		if (!strlen($id)) return '';

		return substr($id, -1);
	}

	public static function upload($identify, $id, $member_id = 0, $old_filename = '', $file_id = 0)
	{
		$config = array(
			'base_path' => sprintf('img/%s/%d', $identify, Site_util::get_middle_dir($id)),
			'prefix'    => sprintf('%s_%d_', $identify, $id),
			'sizes'     => Config::get('site.upload_files.img.'.$identify.'.sizes'),
		);
		if ($old_filename) $config['old_filename'] = $old_filename;
		$uploader = new Site_uploader($config);
		$uploaded_file = $uploader->upload();

		$file = ($file_id) ? Model_File::find()->where('id', $file_id)->get_one() : new Model_File;
		$file->name = $uploaded_file['new_filename'];
		$file->filesize = $uploaded_file['size'];
		$file->original_filename = $uploaded_file['filename'].'.'.$uploaded_file['extension'];
		$file->type = $uploaded_file['type'];
		if ($member_id) $file->member_id = $member_id;
		$file->save();

		return $file->id;
	}

	public static function get_upload_path($type, $filename, $is_dir = false)
	{
		$parts = explode('_', $filename);
		if (count($parts) < 3) return false;

		$dirs = array(
			PRJ_UPLOAD_DIRNAME,
			$type,
			$parts[0],
			Site_util::get_middle_dir($parts[1]),
		);
		$dir_path = implode('/', $dirs);

		if ($is_dir) return $dir_path;

		return $dir_path.'/'.$filename;
	}
}
