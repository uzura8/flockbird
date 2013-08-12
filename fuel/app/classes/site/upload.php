<?php

class Site_Upload
{
	public static function get_upload_split_dir_name($id)
	{
		if (!strlen($id)) return 'all';

		$num_of_digits = strlen(Config::get('site.upload.num_of_split_dirs') - 1);
		if ($num_of_digits < 1) return 'all';

		$cut_num = ($num_of_digits) * -1;
		$sprit_id = substr($id, $cut_num);

		if (strlen($sprit_id) < $num_of_digits)
		{
			$sprit_id = sprintf('%0'.$num_of_digits.'d', $sprit_id);
		}

		return $sprit_id;
	}

	public static function get_accept_format($type = 'img')
	{
		return array_keys(Config::get('site.upload.types.'.$type.'.accept_format'));
	}

	public static function split_file_object2vars($file)
	{
		if (empty($file)) return false;

		$filename = '';
		$filepath = '';
		if (is_object($file))
		{
			if (!empty($file->name)) $filename = $file->name;
			if (!empty($file->path)) $filepath = $file->path;
		}
		elseif (is_array($file))
		{
			if (!empty($file['name'])) $filename = $file['name'];
			if (!empty($file['path'])) $filepath = $file['path'];
		}
		else
		{
			$filepath = $file;
		}

		return array($filepath, $filename);
	}

	public static function check_filepath_format($filepath)
	{
		if (empty($filepath)) return false;
		return (bool)preg_match(self::get_filepath_format(), $filepath);
	}

	public static function get_filepath_format()
	{
		$ids = array_keys(Config::get('site.upload.types.img.types'));
		return '#('.implode('|', $ids).')/[0-9]+#i';
	}

	public static function get_filepath($file_cate, $split_criterion_id)
	{
		return sprintf('%s/%s/', $file_cate, self::get_upload_split_dir_name($split_criterion_id));
	}

	public static function upload($file_cate, $split_criterion_id, $member_id = 0, $member_filesize_total = 0, $old_file = array(), $file_id = 0)
	{
		$file = ($file_id) ? Model_File::find($file_id) : new Model_File;
		if (empty($file)) $file = new Model_File;

		$filepath = self::get_filepath($file_cate, $split_criterion_id);
		$config = array(
			'file_type'   => 'img',
			'filepath'    => $filepath,
			'sizes'       => Config::get('site.upload.types.img.types.'.$file_cate.'.sizes', array()),
			'max_size'    => Config::get('site.upload.types.img.types.'.$file_cate.'.max_size', 0),
			'resize_type' => Config::get('site.upload.types.img.types.'.$file_cate.'.resize_type', 0),
		);
		if (PRJ_IS_LIMIT_UPLOAD_FILE_SIZE && $member_id)
		{
			$config['member_id'] = $member_id;

			$config['file_size_limit'] = Config::get('site.upload.accepted_filesize.small.limit');// default
			$config['member_filesize_total'] = $member_filesize_total;
			if ($file_id) $config['old_file_size'] = $file->filesize;
		}
		list($old_filepath, $old_filename) = self::split_file_object2vars($old_file);
		if (!empty($filepath) && !empty($filename)) $config['old_filepath_name'] = $old_filepath.$old_filename;
		$uploader = new Site_uploader($config);
		$uploaded_file = $uploader->upload();

		$file->name = $uploaded_file['new_filename'];
		$file->path = $filepath;
		$file->filesize = $uploaded_file['size'];
		$file->original_filename = $uploaded_file['name'];
		$file->type = $uploaded_file['type'];
		if ($member_id) $file->member_id = $member_id;
		if ($uploaded_file['exif'])
		{
			$exif = $uploaded_file['exif'];
			if (!empty($exif['DateTimeOriginal'])) $file->shot_at = date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
			$file->exif = serialize($exif);
		}
		//if (empty($file->shot_at)) $file->shot_at = date('Y-m-d H:i:s');

		$file->save();

		return $file;
	}

	public static function remove_images($filepath, $filename)
	{
		$file = self::get_uploaded_file_real_path($filepath, $filename);
		Util_file::remove($file);

		$file_cate = self::get_file_cate_from_filepath($filepath);
		$sizes = Config::get('site.upload.types.img.types.'.$file_cate.'.sizes', array());
		foreach ($sizes as $size)
		{
			$file = self::get_uploaded_file_real_path($filepath, $filename, $size);
			Util_file::remove($file);
		}

		return true;
	}

	public static function get_file_cate_from_filepath($filepath)
	{
		$parts = explode('/', $filepath);
		if (count($parts) < 1) return false;

		return $parts[0];
	}

	public static function conv_size_str_to_array($size_string)
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
		$size  = filesize($file);

		$max = self::conv_size_str_to_array($max_size);
		if ($width <= $max['width'] && $height <= $max['height']) return $size;

		Util_file::resize($file, $file, $max['width'], $max['width'], $resize_type);

		return filesize($file);
	}

	public static function get_image_resize_type($file_cate)
	{
		$resize_type = Config::get('site.upload.types.img.types.'.$file_cate.'.resize_type');
		if (empty($resize_type)) return 'relative';

		return $resize_type;
	}

	public static function get_uploaded_file_uri_path($filepath = '', $filename = '', $size = 'raw', $file_type = 'img')
	{
		if ($size == 'row')
		{
			$uri_path = Config::get('site.upload.types.'.$file_type.'.root_path.raw_dir');
		}
		else
		{
			$uri_path = Config::get('site.upload.types.'.$file_type.'.root_path.cache_dir').$size.'/';
		}
		if ($filepath) $uri_path .= $filepath;
		if ($filepath && $filename) $uri_path .= $filename;

		return $uri_path;
	}

	public static function get_uploaded_file_real_path($filepath, $filename, $size = 'raw', $file_type = 'img')
	{
		if ($size == 'raw')
		{
			$path = Config::get('site.upload.types.'.$file_type.'.raw_file_path').$filepath.$filename;
		}
		else
		{
			$path = PRJ_PUBLIC_DIR.Config::get('site.upload.types.'.$file_type.'.root_path.cache_dir').$size.'/'.$filepath.$filename;
		}

		return $path;
	}

	public static function check_uploaded_file_exists($filepath, $filename, $size = 'raw', $type = 'img')
	{
		$real_path = self::get_uploaded_file_real_path($filepath, $filename, $size, $type);

		return file_exists($real_path);
	}

	public static function check_and_make_uploaded_dir($dir, $check_dir_level = 7)
	{
		if ($target_path = Util_file::check_exists_file_path($dir, $check_dir_level))
		{
			Util_file::make_dir_recursive($dir);
			Util_file::chmod_recursive($target_path, 0777);
		}

		return true;
	}

	public static function setup_uploaded_dir($file_cate, $split_criterion_id, $is_tmp = false)
	{
		$filepath = self::get_filepath($file_cate, $split_criterion_id);

		$key = $is_tmp ? 'site.upload.types.img.tmp.raw_file_path' : 'site.upload.types.img.raw_file_path';
		$real_path_raw = \Config::get($key);

		self::check_and_make_uploaded_dir($real_path_raw.$filepath);

		$key = $is_tmp ? 'site.upload.types.img.tmp.root_path.cache_dir' : 'site.upload.types.img.root_path.cache_dir';
		$real_path_cache = PRJ_PUBLIC_DIR.\Config::get($key);

		$key = $is_tmp ? 'site.upload.types.img.types.'.$file_cate.'.sizes_tmp' : 'site.upload.types.img.types.'.$file_cate.'.sizes';
		$sizes = \Config::get($key);
		foreach ($sizes as $size)
		{
			$dir = sprintf('%s%s/%s', $real_path_cache, $size, $filepath);
			self::check_and_make_uploaded_dir($dir);
		}

		return true;
	}

	public static function get_upload_handler_options($filepath, $script_url, $file_cate, $tmp_hash_key = 'tmp_hash')
	{
		$tmp_hash = Input::post_get($tmp_hash_key, '');
		$contents = Input::post_get('contents', '');
		$is_tmp = self::check_is_temp_upload($tmp_hash_key);

		$real_path_raw   = Config::get('site.upload.types.img.raw_file_path');
		$real_path_cache = PRJ_PUBLIC_DIR.\Config::get('site.upload.types.img.root_path.cache_dir');
		$uri_path_cache  = '/'.Config::get('site.upload.types.img.root_path.cache_dir');
		if ($is_tmp)
		{
			$real_path_raw   = Config::get('site.upload.types.img.tmp.raw_file_path');
			$real_path_cache = PRJ_PUBLIC_DIR.Config::get('site.upload.types.img.tmp.root_path.cache_dir');
			$uri_path_cache  = '/'.Config::get('site.upload.types.img.tmp.root_path.cache_dir');
		}

		$options = array();
		$options['is_tmp']       = $is_tmp;
		$options['tmp_hash_key'] = $tmp_hash_key;
		$options['script_url']   = $script_url;
		$options['upload_dir']   = $real_path_raw;
		$options['upload_dir_cache'] = $real_path_cache;
		$options['upload_url']       = $uri_path_cache;
		$options['max_size']         = Config::get('site.upload.types.img.'.$file_cate.'.max_size', Config::get('site.upload.types.img.defaults.max_size'));
		$options['max_file_size']    = PRJ_UPLOAD_MAX_FILESIZE;
		$options['max_number_of_files'] = PRJ_MAX_FILE_UPLOADS;

		$config_upload_files = Config::get('site.upload.types.img.types.'.$file_cate);
		$sizes = $is_tmp ? $config_upload_files['sizes_tmp'] : $config_upload_files['sizes'];
		$thumbnail_size = $config_upload_files['default_size'];
		$options['image_versions'] = array();
		foreach ($sizes as $size)
		{
			$key = ($size == $thumbnail_size)? 'thumbnail' : $size;
			list($width, $height) = explode('x', $size);
			$options['image_versions'][$key] = array(
				'size' => $size,
				'upload_dir' => sprintf('%s%s/%s', $real_path_cache, $size, $filepath),
				'upload_url' => sprintf('%s%s/%s', $uri_path_cache, $size, $filepath),
				'max_width'  => $width,
				'max_height' => $height,
			);
		}

		return $options;
	}

	public static function check_is_temp_upload($key = 'tmp_hash')
	{
		if (!\Input::post_get($key, '')) return false;

		if (!$contents = \Input::post_get('contents', '')) return false;
		if (!in_array($contents, array('note'))) return false;

		return true;
	}

	public static function check_is_temp_accepted_contents($target)
	{
		if (empty($target)) return false;

		return in_array($target, Config::get('site.upload.tmp_file.accepted_contents'));
	}
}
