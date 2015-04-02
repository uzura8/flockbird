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

	public static function remove_files_all($filename, $is_tmp = false)
	{
		return self::remove_images($filename, $is_tmp) && self::remove_raw_file($filename, $is_tmp);
	}

	public static function remove_images($filename, $is_tmp = false)
	{
		$file = self::get_uploaded_file_path($filename, 'raw', 'img', $is_tmp);
		Util_file::remove($file);

		if (conf('upload.types.img.thumbnailsDeleteType', null, 'sameTime') == 'sameTime')
		{
			$file_cate = self::get_file_cate_from_filename($filename);
			$sizes = self::get_sizes_all4file_cate($file_cate, $is_tmp);
			foreach ($sizes as $key => $size)
			{
				$file = self::get_uploaded_file_path($filename, $is_tmp ? $key : $size, 'img', $is_tmp);
				Util_file::remove($file);
			}
		}

		return true;
	}

	public static function remove_raw_file($filename, $is_tmp = false)
	{
		$file = self::get_uploaded_file_path($filename, 'raw', 'file', $is_tmp);
		Util_file::remove($file);

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

	public static function get_filepath_prefix_from_filename($filename)
	{
		return Util_file::get_path_partial(str_replace('_', '/', $filename), 2, 1).'/';
	}

	public static function convert_filename2filepath($filename)
	{
		return str_replace('_', '/', $filename);
	}

	public static function convert_filepath2filename($filepath)
	{
		return str_replace('/', '_', $filepath);
	}

	public static function get_file_cate_from_filename($filename)
	{
		$parts = explode('_', $filename);
		if (count($parts) < 1) return false;

		return $parts[0];
	}

	public static function get_file_cate_from_filepath($filepath)
	{
		$parts = explode('/', $filepath);
		if (count($parts) < 1) return false;

		return $parts[0];
	}

	public static function get_file_name_from_file_path($file_path)
	{
		$parts = explode('/', $file_path);
		if (count($parts) < 1) return false;

		return array_pop($parts);
	}

	public static function get_filepath_from_file_path($file_path)
	{
		return Util_File::get_path_partial($file_path, 3);
	}

	public static function get_filename_from_file_path($file_path)
	{
		return self::convert_filepath2filename(self::get_filepath_from_file_path($file_path));
	}

	public static function get_file_dir_path_from_file_path($file_path)
	{
		$parts = explode('/', $file_path);
		if (count($parts) < 1) return false;
		array_pop($parts);

		return implode('/', $parts);
	}

	public static function check_filepath_prefix_format($filepath_prefix)
	{
		if (empty($filepath_prefix)) return false;
		return (bool)preg_match(self::get_filepath_prefix_format(), $filepath_prefix);
	}

	public static function get_filepath_prefix_format()
	{
		$ids = array_keys(conf('upload.types.img.types'));
		return '#('.implode('|', $ids).')/[0-9]+#i';
	}

	public static function get_filename_prefix($file_cate, $split_criterion_id)
	{
		return sprintf('%s_%s_', $file_cate, self::get_upload_split_dir_name($split_criterion_id));
	}

	public static function get_filepath_prefix($file_cate, $split_criterion_id)
	{
		return sprintf('%s/%s/', $file_cate, self::get_upload_split_dir_name($split_criterion_id));
	}

	public static function change_filename_prefix($filename, $new_prefix)
	{
		$parts = explode('_', $filename);
		if (count($parts) != 3) throw new FuelException('File name is invalid.');

		return $new_prefix.$parts[2];
	}

	//public static function get_file_cate_from_table($table)
	//{
	//	switch ($table)
	//	{
	//		case 'member':
	//			return 'm';
	//			break;
	//		case 'album_image':
	//			return 'ai';
	//			break;
	//		case 'news_image':
	//			return 'nw';
	//			break;
	//		case 'news_file':
	//			return 'nw';
	//			break;
	//		default :
	//			break;
	//	}

	//	return false;
	//}

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

	public static function get_uploaded_path($size = 'raw', $file_type = 'img', $is_tmp = false, $is_uri = false, $suffix = '')
	{
		if (!in_array($file_type, array('img', 'file')))
		{
			throw new InvalidArgumentException('Second parameter is invalid.');
		}

		$key = 'upload.types.'.$file_type;
		if ($is_tmp) $key .= '.tmp';
		if ($is_uri)
		{
			$key .= '.root_path';
			if ($size == 'raw') return conf($key.'.raw_dir').$suffix;

			return conf($key.'.cache_dir').$size.'/'.$suffix;
		}

		if ($size == 'raw') return conf($key.'.raw_file_path').$suffix;

		return DOCROOT.conf($key.'.root_path.cache_dir').$size.'/'.$suffix;
	}

	public static function get_uploaded_file_path($filename, $size = 'raw', $file_type = 'img', $is_tmp = false, $is_uri = false)
	{
		return self::get_uploaded_path($size, $file_type, $is_tmp, $is_uri).self::convert_filename2filepath($filename);
	}

	public static function check_uploaded_file_exists($filename, $size = 'raw', $type = 'img', $is_tmp = false)
	{
		$real_path = self::get_uploaded_file_path($filename, $size, $type, $is_tmp);

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

	public static function get_uploader_info($file_cate, $filepath_prefix, $is_tmp = false, $type = 'img', $with_thumbnails = true)
	{
		$upload_dir = self::get_uploaded_path('raw', $type, $is_tmp, false, $filepath_prefix);
		$upload_uri = self::get_uploaded_path('raw', $type, $is_tmp, true, $filepath_prefix);
		$thumbnail_sizes = array();
		if ($type == 'img' && $with_thumbnails)
		{
			$conf_key = $is_tmp ? 'upload.types.img.tmp.sizes.thumbnail' : 'upload.types.img.types.'.$file_cate.'.sizes.thumbnail';
			$thumbnail_sizes = Site_Upload::conv_size_str_to_array(conf($conf_key));
		}

		$info = array(
			'upload_dir' => $upload_dir,
			'upload_uri' => $upload_uri,
			'upload_url' => Uri::create($upload_uri),
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
		$filename_prefix = \Site_Upload::get_filename_prefix($file_cate, $split_criterion_id);
		$filepath_prefix = self::convert_filename2filepath($filename_prefix);

		$options = self::get_uploader_info($file_cate, $filepath_prefix);
		$options['member_id'] = $member_id;
		$options['filename_prefix'] = $filename_prefix;

		return $options;
	}

	public static function get_upload_handler_options($member_id, $is_admin = false, $is_tmp = true, $file_cate = null, $split_criterion_id = 0, $is_multiple_upload = true, $upload_type = 'img', $with_accept_sizes = false)
	{
		if (!$split_criterion_id) $split_criterion_id = $member_id;
		if (!$file_cate) $file_cate = $is_admin ? 'au' : 'm';
		$filename_prefix = self::get_filename_prefix($file_cate, $split_criterion_id);
		$filepath_prefix = self::convert_filename2filepath($filename_prefix);
		$uploader_info   = self::get_uploader_info($file_cate, $filepath_prefix, $is_tmp, $upload_type);
		$options = array(
			'storage_type'    => conf('upload.storageType'),
			'is_tmp'          => $is_tmp,
			'max_file_size'   => FBD_UPLOAD_MAX_FILESIZE,
			'max_number_of_files' => $is_multiple_upload ? FBD_MAX_FILE_UPLOADS : 1,
			'upload_dir'      => $uploader_info['upload_dir'],
			'upload_url'      => $uploader_info['upload_url'],
			'upload_uri'      => $uploader_info['upload_uri'],
			'mkdir_mode'      => conf('upload.mkdir_mode'),
			'member_id'       => $member_id,
			'upload_type'     => $upload_type,
			'user_type'       => $is_admin ? 1 : 0,
			'filename_prefix' => $filename_prefix,
			'image_versions'  => array(),
			'is_clear_exif_on_file' => false,
			'is_save_exif_to_db'    => false,
			'exif_accept_tags'      => array(),
			'exif_ignore_tags'      => array(),
		);
		if ($upload_type == 'img')
		{
			$options['image_library'] =  0;
			if (FBD_IMAGE_DRIVER == 'imagemagick')
			{
				$options['image_library'] = 1;
				$options['convert_bin'] = FBD_IMAGE_IMGMAGICK_PATH.'convert';
			}
			$options['image_versions'] = array(
				'' => array(
					'auto_orient' => true
				),
			);
			$options['image_versions']['thumbnail'] = array(
				'upload_dir' => self::get_uploaded_path('thumbnail', $upload_type, $is_tmp, false, $filepath_prefix),
				'upload_url' => Site_Util::get_media_uri(self::get_uploaded_path('thumbnail', $upload_type, $is_tmp, true, $filepath_prefix), true),
				'max_width'  => $uploader_info['thumbnail_sizes']['width'],
				'max_height' => $uploader_info['thumbnail_sizes']['height'],
				'crop' => true,
			);

			if ($with_accept_sizes && $accept_size = conf('upload.types.img.types.'.$file_cate.'.sizes'))
			{
				$accept_size[] = 'raw';
				$options['accept_sizes'] =  array_unique($accept_size);
			}

			$options['is_clear_exif_on_file'] = conf('isClearFromFile', 'exif');
			$options['is_save_exif_to_db']    = conf('isSaveToDb.isEnabled', 'exif');
			if (conf('isSaveToDb.filterTags.accept.isEnabled', 'exif')) $options['exif_accept_tags'] = conf('isSaveToDb.filterTags.accept.tags', 'exif');
			if (conf('isSaveToDb.filterTags.ignore.isEnabled', 'exif')) $options['exif_ignore_tags'] = conf('isSaveToDb.filterTags.ignore.tags', 'exif');
		}

		return $options;
	}

	public static function get_file_objects($model_objs, $parent_id, $is_admin = null, $member_id = null, $type = 'img', $with_accept_sizes = false)
	{
		if (!$key = Util_Array::get_first_key($model_objs)) return array();
		$file_cate = $model_objs[$key]->get_image_prefix();

		$options = self::get_upload_handler_options($member_id, $is_admin, false, $file_cate, $parent_id, true, $type, $with_accept_sizes);
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

	public static function make_thumbnails($raw_file_path, $filepath_prefix, $is_check_and_make_dir = true, $additional_sizes_key = null)
	{
		$file_cate = self::get_file_cate_from_filepath($filepath_prefix);
		$file_name = self::get_file_name_from_file_path($raw_file_path);
		$sizes = Config::get(sprintf('site.upload.types.img.types.%s.sizes', $file_cate));
		if ($additional_sizes_key)
		{
			$sizes += Config::get(sprintf('site.upload.types.img.types.%s.additional_sizes.%s', $file_cate, $additional_sizes_key), array());
		}
		$cache_dir_path = DOCROOT.conf('upload.types.img.root_path.cache_dir');

		$result = true;
		foreach ($sizes as $size)
		{
			$cache_file_dir_path = sprintf('%s%s/%s', $cache_dir_path, $size, $filepath_prefix);
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
		if ($is_tmp) return conf('upload.types.img.tmp.sizes', null, array());

		$sizes = conf('upload.types.img.types.'.$file_cate.'.sizes', null, array());
		$additional_sizes_list = conf('upload.types.img.types.'.$file_cate.'.additional_sizes', null, array());
		foreach ($additional_sizes_list as $key => $additional_sizes)
		{
			$sizes += $additional_sizes;
		}
		//$sizes[] = conf('upload.types.img.tmp.sizes.thumbnail');

		return $sizes;
	}

	public static function make_unique_filename($extention, $prefix = '', $original_filename = '', $retry_count = 3)
	{
		$name = self::make_filename($extention, $prefix, $original_filename);
		$i = 0;
		while(Model_File::check_name_exists($name))
		{
			$name = self::make_filename($extention, $prefix, $original_filename);
			if ($i == $retry_count) return false;
			$i++;
		}

		return $name;
	}

	public static function make_filename($extention, $prefix = '', $original_filename = '', $with_extension = true, $delimitter = '')
	{
		$filename = Util_string::get_random($original_filename, $extention);
		if ($prefix) $prefix .= $delimitter;
		if ($with_extension) $filename .= '.'.$extention;

		return $prefix.$filename;
	}

	public static function get_bin_from_storage($filename, $strage_type = null, $upload_type = 'img')
	{
		if (!$strage_type) $strage_type = conf('upload.storageType');
		if (!in_array($strage_type, array('db', 'S3'))) throw new InvalidArgumentException('Second parameter is invalid.');

		return $strage_type == 'db' ? Model_FileBin::get_bin4name($filename) : file_get_contents(Site_S3::get_url($filename, $upload_type));
	}

	public static function make_raw_file_from_storage($filename, $file_path, $strage_type = null, $upload_type = 'img')
	{
		if (!$bin = static::get_bin_from_storage($filename, $strage_type, $upload_type)) return false;
		if (!$file_dir_path = self::get_file_dir_path_from_file_path($file_path)) return false;
		if (!self:: check_and_make_uploaded_dir($file_dir_path)) return false;
		if (file_put_contents($file_path, $bin)) return $file_path;

		return false;
	}

	public static function clear_exif($path, $driver = null)
	{
		if (!$driver) $driver = Config::get('image.driver');
		if (FBD_IMAGE_DRIVER == 'imagemagick')
		{
			$command = FBD_IMAGE_IMGMAGICK_PATH.'convert';
			$params  = sprintf('"%s" -strip "%s"', $path, $path);
			Util_Toolkit::exec_command($command, $params, false, true);
		}
		elseif (FBD_IMAGE_DRIVER == 'imagick')
		{
			$im = new \Imagick($path);
			$im->stripImage();
			$im->writeImage($path);
			$im->destroy();
		}
		else
		{
			Util_file::resave($path);
		}

		return File::get_size($path);
	}
}
