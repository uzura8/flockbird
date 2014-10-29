<?php

class Site_Upload
{
	public static function get_upload_split_dir_name($id)
	{
		if (!strlen($id)) return 'all';

		$num_of_digits = strlen(conf('upload.num_of_split_dirs') - 1);
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
		return array_keys(conf('upload.types.'.$type.'.accept_format'));
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
		$ids = array_keys(conf('upload.types.img.types'));
		return '#('.implode('|', $ids).')/[0-9]+#i';
	}

	public static function get_filepath($file_cate, $split_criterion_id)
	{
		return sprintf('%s/%s/', $file_cate, self::get_upload_split_dir_name($split_criterion_id));
	}

	public static function remove_images($filepath, $filename, $is_tmp = false)
	{
		$file = self::get_uploaded_file_real_path($filepath, $filename, 'raw', 'img', $is_tmp);
		Util_file::remove($file);

		$file_cate = self::get_file_cate_from_filepath($filepath);
		$sizes = self::get_sizes_all4file_cate($file_cate, $is_tmp);
		foreach ($sizes as $key => $size)
		{
			$file = self::get_uploaded_file_real_path($filepath, $filename, $is_tmp ? $key : $size, 'img', $is_tmp);
			Util_file::remove($file);
		}

		return true;
	}

	public static function remove_files(array $files)
	{
		$i = 0;
		foreach ($files as $file)
		{
			if (!self::remove_images($file['path'], $file['name'])) continue;
			$i++;
		}

		return $i;
	}

	public static function get_file_cate_from_filepath($filepath)
	{
		$parts = explode('/', $filepath);
		if (count($parts) < 1) return false;

		return $parts[0];
	}

	public static function get_file_cate_from_table($table)
	{
		switch ($table)
		{
			case 'member':
				return 'm';
				break;
			case 'album_image':
				return 'ai';
				break;
			case 'news_image':
				return 'nw';
				break;
			case 'news_file':
				return 'nw';
				break;
			default :
				break;
		}

		return false;
	}

	public static function get_name_cate_from_file_path($file_path)
	{
		$parts = explode('/', $file_path);
		if (count($parts) < 1) return false;

		return array_pop($parts);
	}

	public static function conv_size_str_to_array($size_string)
	{
		$items = explode('x', $size_string);

		$sizes = array();
		$sizes['width']  = !empty($items[0]) ? (int)$items[0] : 0;
		$sizes['height'] = !empty($items[1]) ? (int)$items[1] : 0;
		$resize_type_id  = !empty($items[2]) ? (int)$items[2] : '';
		$sizes['resize_type'] = self::conv_resize_type_id($resize_type_id);

		return $sizes;
	}

	public static function conv_resize_type_id($resize_type_id)
	{
		switch ($resize_type_id)
		{
			case 'c':
				return 'crop';
				break;
			case 'a':
				return 'absolute';
				break;
			case 'r':
				return 'relative';
				break;
		}

		return 'relative';
	}

	public static function check_max_size_and_resize($file, $max_size)
	{
		$sizes = Image::sizes($file);
		$size  = filesize($file);

		$max = self::conv_size_str_to_array($max_size);
		if ($sizes->width <= $max['width'] && $sizes->height <= $max['height']) return $size;

		Util_file::resize($file, $file, $max['width'], $max['width']);

		return filesize($file);
	}

	public static function get_uploaded_file_uri_path($filepath = '', $filename = '', $size = 'raw', $file_type = 'img')
	{
		if ($size == 'row')
		{
			$uri_path = conf('upload.types.'.$file_type.'.root_path.raw_dir');
		}
		else
		{
			$uri_path = conf('upload.types.'.$file_type.'.root_path.cache_dir').$size.'/';
		}
		if ($filepath) $uri_path .= $filepath;
		if ($filepath && $filename) $uri_path .= $filename;

		return $uri_path;
	}

	public static function get_uploaded_file_real_path($filepath, $filename, $size = 'raw', $file_type = 'img', $is_tmp = false)
	{
		$key = 'site.upload.types.'.$file_type;
		if ($is_tmp) $key .= '.tmp';
		if ($size == 'raw')
		{
			$key .= '.raw_file_path';
			$path = Config::get($key).$filepath.$filename;
		}
		else
		{
			if ($is_tmp)
			{
				$key .= '.raw_file_path';
				$path = Config::get($key).$filepath.$size.'/'.$filename;
			}
			else
			{
				$key .= '.root_path.cache_dir';
				$path = PRJ_PUBLIC_DIR.Config::get($key).$size.'/'.$filepath.$filename;
			}
		}

		return $path;
	}

	public static function check_uploaded_file_exists($filepath, $filename, $size = 'raw', $type = 'img')
	{
		$real_path = self::get_uploaded_file_real_path($filepath, $filename, $size, $type);

		return file_exists($real_path);
	}

	public static function check_and_make_uploaded_dir($dir, $check_dir_level = null, $mode = null)
	{
		if (!$check_dir_level) $check_dir_level = conf('upload.check_and_make_dir_level');
		if (!$mode) $mode = conf('upload.mkdir_mode');
		if ($target_path = Util_file::check_exists_file_path($dir, $check_dir_level))
		{
			if (false === Util_file::make_dir_recursive($dir, $mode)) return false;
			Util_file::chmod_recursive($target_path, $mode);
		}

		return true;
	}

	public static function get_uploader_info($file_cate, $split_criterion_id, $is_tmp = false, $type = 'img', $with_thumbnails = true)
	{
		$filepath = \Site_Upload::get_filepath($file_cate, $split_criterion_id);
		$thumbnail_sizes = array();
		if ($is_tmp)
		{
			if ($type == 'img' && $with_thumbnails) $thumbnail_sizes = Site_Upload::conv_size_str_to_array(conf('upload.types.img.tmp.sizes.thumbnail'));
			$upload_dir = conf('upload.types.'.$type.'.tmp.raw_file_path').$filepath;
			$upload_uri = conf('upload.types.'.$type.'.tmp.root_path.raw_dir').$filepath;
		}
		else
		{
			if ($type == 'img' && $with_thumbnails) $thumbnail_sizes = Site_Upload::conv_size_str_to_array(conf('upload.types.img.types.'.$file_cate.'.sizes.thumbnail'));
			$upload_dir      = conf('upload.types.'.$type.'.raw_file_path').$filepath;
			$upload_uri      = conf('upload.types.'.$type.'.root_path.raw_dir').$filepath;
		}
		$upload_url = Uri::create($upload_uri);

		$info = array(
			'filepath'   => $filepath,
			'upload_dir' => $upload_dir,
			'upload_uri' => $upload_uri,
			'upload_url' => $upload_url,
		);
		if (!empty($thumbnail_sizes)) $info['thumbnail_sizes'] = $thumbnail_sizes;

		return $info;
	}

	public static function check_file_format_is_accepted($file_format, $upload_type = 'img')
	{
		if (!in_array($upload_type, array('img', 'file'))) throw new InvalidArgumentException('Second parameter is invalid.');
		$accepted_formats = conf('upload.types.'.$upload_type.'.accept_format');

		return array_search($file_format, $accepted_formats);
	}

	public static function get_uploader_options($member_id, $file_cate = 'm', $split_criterion_id = 0)
	{
		if (!$split_criterion_id) $split_criterion_id = $member_id;
		$options = self::get_uploader_info($file_cate, $split_criterion_id);
		$options['member_id'] = $member_id;

		return $options;
	}

	public static function get_upload_handler_options($member_id, $is_admin = false, $is_tmp = true, $file_cate = null, $split_criterion_id = 0, $is_multiple_upload = true, $upload_type = 'img')
	{
		if (!$split_criterion_id) $split_criterion_id = $member_id;
		if (!$file_cate) $file_cate = $is_admin ? 'au' : 'm';
		$uploader_info = self::get_uploader_info($file_cate, $split_criterion_id, $is_tmp, $upload_type);
		$options = array(
			'max_file_size'  => PRJ_UPLOAD_MAX_FILESIZE,
			'max_number_of_files' => $is_multiple_upload ? PRJ_MAX_FILE_UPLOADS : 1,
			'upload_dir'     => $uploader_info['upload_dir'],
			'upload_url'     => $uploader_info['upload_url'],
			'upload_uri'     => $uploader_info['upload_uri'],
			'mkdir_mode'     => conf('upload.mkdir_mode'),
			'member_id'      => $member_id,
			'upload_type'    => $upload_type,
			'user_type'      => $is_admin ? 1 : 0,
			'filepath'       => $uploader_info['filepath'],
			'is_save_exif'   => false,
			'image_versions' => array(),
		);
		if ($upload_type == 'img')
		{
			$options['is_save_exif'] = conf('upload.types.img.exif.is_use');
			$options['image_versions'] = array(
				'' => array(
					'auto_orient' => true
				),
			);
			$options['image_versions']['thumbnail'] = array(
				'max_width'  => $uploader_info['thumbnail_sizes']['width'],
				'max_height' => $uploader_info['thumbnail_sizes']['height'],
				'crop' => true,
			);
		}

		return $options;
	}

	public static function get_file_objects($model_objs, $parent_id, $is_admin = null, $member_id = null, $type = 'img')
	{
		if (!$key = Util_Array::get_first_key($model_objs)) return array();
		$table = $model_objs[$key]->table();
		$file_cate = Site_Upload::get_file_cate_from_table($table);

		$options = self::get_upload_handler_options($member_id, $is_admin, false, $file_cate, $parent_id, true, $type);
		$uploadhandler = new \MyUploadHandler($options, false);

		return $uploadhandler->get_file_objects_from_related_model($model_objs, \Input::post(($type == 'img') ? 'image_description' : 'file_description'));
	}

	public static function update_image_objs4file_objects($image_objs, $files, $public_flag = null)
	{
		foreach ($files as $file)
		{
			if(empty($image_objs[$file->id])) continue;
			$image_obj = $image_objs[$file->id];

			if ($image_obj->name !== $file->description)
			{
				$image_obj->name = $file->description;
			}
			if (!is_null($public_flag) && $image_obj->public_flag != $public_flag)
			{
				$image_obj->public_flag = $public_flag;
			}

			$image_obj->save();
		}
	}

	public static function make_thumbnails($raw_file_path, $filepath, $is_check_and_make_dir = true, $additional_sizes_key = null)
	{
		$file_cate = self::get_file_cate_from_filepath($filepath);
		$file_name = self::get_name_cate_from_file_path($raw_file_path);
		$sizes = Config::get(sprintf('site.upload.types.img.types.%s.sizes', $file_cate));
		if ($additional_sizes_key)
		{
			$sizes += Config::get(sprintf('site.upload.types.img.types.%s.additional_sizes.%s', $file_cate, $additional_sizes_key), array());
		}
		$cache_dir_path = PRJ_PUBLIC_DIR.conf('upload.types.img.root_path.cache_dir');

		$result = true;
		foreach ($sizes as $size)
		{
			$cache_file_dir_path = sprintf('%s%s/%s', $cache_dir_path, $size, $filepath);
			if ($is_check_and_make_dir) $res = self::check_and_make_uploaded_dir($cache_file_dir_path);
			$cache_file_path = $cache_file_dir_path.$file_name;
			if (self::make_thumbnail($raw_file_path, $cache_file_path, $size) === false) $result = false;// thumbnail の作成
		}

		return $result;
	}

	public static function make_thumbnail($raw_file_path, $thumbnail_file_path, $size_string)
	{
		$size_items = self::conv_size_str_to_array($size_string);

		return Util_file::resize($raw_file_path, $thumbnail_file_path, $size_items['width'], $size_items['height'], $size_items['resize_type']);
	}

	public static function save_image_from_url($image_url, $save_file_path, $max_size = 0, $old_file_path = null)
	{
		if (!$data = file_get_contents($image_url)) throw new FuelException('Get image from url failed.');
		if (!file_put_contents($save_file_path, $data))
		{
			throw new FuelException('Failed to save image.');
		}
		unset($data);
		if ($max_size) Site_Upload::check_max_size_and_resize($save_file_path, $max_size);

		// if exists old_file_path, compare data. if data is same, delete new file and return false;
		if ($old_file_path)
		{
			$new_data = file_get_contents($save_file_path);
			$old_data = file_get_contents($old_file_path);
			if ($new_data == $old_data) return false;
		}
		unset($new_data, $old_data);

		return true;
	}

	public static function get_accepted_max_size($member_id = null)
	{
		return conf('upload.types.img.accepted_max_size.default');
	}

	public static function get_accepted_filesize($member_id = null, $is_return_byte = true)
	{
		$value = conf('upload.accepted_filesize.small.limit');
		if ($is_return_byte) $value = Num::bytes($value);

		return $value;
	}

	public static function get_sizes_all4file_cate($file_cate, $is_tmp = false)
	{
		if ($is_tmp) return conf('upload.types.img.tmp.sizes', array());

		$sizes = conf('upload.types.img.types.'.$file_cate.'.sizes', array());
		$additional_sizes_list = conf('upload.types.img.types.'.$file_cate.'.additional_sizes', array());
		foreach ($additional_sizes_list as $key => $additional_sizes)
		{
			$sizes += $additional_sizes;
		}

		return $sizes;
	}

	public static function get_exif_datetime($exif)
	{
		if (empty($exif['DateTimeOriginal'])) return null;

		if (!$exif_time = \Util_Date::check_is_past($exif['DateTimeOriginal'], null, '-30 years', true)) return null;

		return $exif_time;
	}

	public static function make_file_name($original_filename, $extention, $upload_dir, $retry_count = 3)
	{
		$name = Util_file::make_filename($original_filename, $extention);
		$i = 0;
		while(file_exists($upload_dir.$name))
		{
			$name = Util_file::make_filename($original_filename, $extention);
			if ($i == $retry_count) return false;
			$i++;
		}

		return $name;
	}

	public static function make_raw_file_from_db($filepath, $filename)
	{
		if (!$bin = Model_FileBin::get_bin4file_name($filename)) return false;

		return (bool)file_put_contents(self::get_uploaded_file_real_path($filepath, $filename), $bin);
	}
}
