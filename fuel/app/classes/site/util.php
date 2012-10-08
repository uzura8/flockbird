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

	public static function check_filename_format($filename)
	{
		$ids = array_keys(Config::get('site.upload_files.img'));
		$pattern = '/('.implode('|', $ids).')_[0-9]+_[0-9a-f]+\.(jpg|png|gif)/i';

		return (bool)preg_match($pattern, $filename);
	}

	public static function get_upload_file_path($filename, $size)
	{
		$identify = Util_string::get_exploded($filename);
		$sizes = Config::get('site.upload_files.img.'.$identify.'.sizes');
		if (empty($sizes) || !in_array($size, $sizes)) $size = '50x50';

		$uri_basepath = Site_util::get_upload_path('img', $filename, true);
		$uri_path = sprintf('%s/%s/%s', $uri_basepath, $size, $filename);

		return sprintf('%s/%s', PRJ_UPLOAD_DIR, $uri_path);
	}

	public static function upload($identify, $id, $member_id = 0, $member_filesize_total = 0, $old_filename = '', $file_id = 0)
	{
		$file = ($file_id) ? Model_File::find()->where('id', $file_id)->get_one() : new Model_File;

		$config = array(
			'base_path'   => sprintf('img/%s/%d', $identify, Site_util::get_middle_dir($id)),
			'prefix'      => sprintf('%s_%d_', $identify, $id),
			'sizes'       => Config::get('site.upload_files.img.'.$identify.'.sizes', array()),
			'max_size'    => Config::get('site.upload_files.img.'.$identify.'.max_size', 0),
			'resize_type' => Config::get('site.upload_files.img.'.$identify.'.resize_type', 'relative'),
		);
		if (PRJ_IS_LIMIT_UPLOAD_FILE_SIZE && $member_id)
		{
			$config['member_id'] = $member_id;

			$accepted_upload_filesize_type = 'small';// default
			$config['file_size_limit'] = Config::get('site.accepted_upload_filesize_type.'.$accepted_upload_filesize_type.'.limit_size');
			$config['member_filesize_total'] = $member_filesize_total;
			if ($file_id) $config['old_file_size'] = $file->filesize;
		}

		if ($old_filename) $config['old_filename'] = $old_filename;
		$uploader = new Site_uploader($config);
		$uploaded_file = $uploader->upload();

		$file->name = $uploaded_file['new_filename'];
		$file->filesize = $uploaded_file['size'];
		$file->original_filename = $uploaded_file['filename'].'.'.$uploaded_file['extension'];
		$file->type = $uploaded_file['type'];
		if ($member_id) $file->member_id = $member_id;
		if ($uploaded_file['exif'])
		{
			$exif = $uploaded_file['exif'];
			if (!empty($exif['DateTimeOriginal'])) $file->shot_at = date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
			$file->exif = serialize($exif);
		}
		if (empty($file->shot_at)) $file->shot_at = date('Y-m-d H:i:s');

		$file->save();

		return $file->id;
	}

	public static function remove_images($identify, $id, $file_name = '')
	{
		$base_path = sprintf('%s/img/%s/%d', PRJ_UPLOAD_DIR, $identify, Site_util::get_middle_dir($id));
		$sizes = Config::get('site.upload_files.img.'.$identify.'.sizes', array());
		foreach ($sizes as $size)
		{
			$file = sprintf('%s/%s/%s', $base_path, $size, $file_name);
			Util_file::remove($file);
		}

		return true;
	}

	public static function get_upload_path($type, $filename, $is_dir = false)
	{
		$parts = explode('_', $filename);
		if (count($parts) < 3) return false;

		$dir_path = self::get_upload_uri_base_path($type, $parts[0], $parts[1]);

		if ($is_dir) return $dir_path;

		return $dir_path.'/'.$filename;
	}

	public static function get_upload_uri_base_path($type, $identifer, $id)
	{
		$dirs = array(
			PRJ_UPLOAD_DIRNAME,
			$type,
			$identifer,
			Site_util::get_middle_dir($id),
		);

		return implode('/', $dirs);
	}

	public static function convert_sizes($size_string)
	{
		list($width, $height) = explode('x', $size_string);
		$sizes = array();
		$sizes['width']  = $width;
		$sizes['height'] = $height;

		return $sizes;
	}

	public static function check_max_size_and_resize($file, $max_size, $width, $height, $resize_type = 'relative')
	{
		$sizes = Image::sizes($file);

		$max = self::convert_sizes($max_size);
		if ($width > $max['width'] || $height > $max['height'])
		{
			Util_file::resize($file, $file, $max['width'], $max['width'], $resize_type);
			return filesize($file);
		}

		return false;
	}
}
