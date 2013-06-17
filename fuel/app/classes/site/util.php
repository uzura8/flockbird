<?php
class Site_util
{
	public static function get_module_name()
	{
		return (isset(Request::main()->route->module))? Request::main()->route->module : '';
	}

	public static function get_controller_name()
	{
		if (!isset(Request::main()->route->controller)) return '';

		return Str::lower(preg_replace('/^[a-zA-Z0-9_]+\\\Controller_/', '', Request::main()->route->controller));
	}

	public static function get_action_name()
	{
		return (isset(Request::main()->route->action))? Request::main()->route->action : '';
	}

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
		return (bool)preg_match(self::get_filename_format(), $filename);
	}

	public static function get_filename_format()
	{
		$ids = array_keys(Config::get('site.upload_files.img.type'));
		return '/('.implode('|', $ids).')_[0-9]+_[0-9a-f]+\.(jpg|png|gif)/i';
	}

	public static function get_upload_file_path($filename, $size)
	{
		$identifier = Util_string::get_exploded($filename);
		$sizes = Config::get('site.upload_files.img.type.'.$identifier.'.sizes');
		if (empty($sizes) || !in_array($size, $sizes)) $size = '50x50';

		$uri_basepath = Site_util::get_upload_path('img', $filename, true);
		$uri_path = sprintf('%s/%s/%s', $uri_basepath, $size, $filename);

		return sprintf('%s/%s', PRJ_UPLOAD_DIR, $uri_path);
	}

	public static function upload($identifier, $id, $member_id = 0, $member_filesize_total = 0, $old_filename = '', $file_id = 0)
	{
		$file = ($file_id) ? Model_File::find()->where('id', $file_id)->get_one() : new Model_File;

		$config = array(
			'base_path'   => sprintf('img/%s/%d', $identifier, Site_util::get_middle_dir($id)),
			'prefix'      => sprintf('%s_%d_', $identifier, $id),
			'sizes'       => Config::get('site.upload_files.img.type.'.$identifier.'.sizes', array()),
			'max_size'    => Config::get('site.upload_files.img.type.'.$identifier.'.max_size', 0),
			'resize_type' => Config::get('site.upload_files.img.type.'.$identifier.'.resize_type', 'relative'),
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

	public static function remove_images($identifier, $id, $file_name = '')
	{
		$base_path = sprintf('%s/img/%s/%d', PRJ_UPLOAD_DIR, $identifier, Site_util::get_middle_dir($id));
		$sizes = Config::get('site.upload_files.img.type.'.$identifier.'.sizes', array());
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

	public static function get_image_resize_type($identifier)
	{
		$resize_type = Config::get('site.upload_files.img.type'.$identifier.'resize_type');
		if (empty($resize_type)) return 'relative';

		return $resize_type;
	}

	public static function get_uploaded_file_uri_path($filename, $size = 'raw', $type = 'img')
	{
		$uri_basepath = self::get_upload_path($type, $filename, true);

		return sprintf('%s/%s/%s', $uri_basepath, $size, $filename);
	}

	public static function check_uploaded_file_exists($filename, $size = 'raw', $type = 'img')
	{
		$uri_path = self::get_uploaded_file_uri_path($filename, $size, $type);
		$file = sprintf('%s/%s', PRJ_PUBLIC_DIR, $uri_path);

		return file_exists($file);
	}

	public static function get_form_instance($model_obj = null, $name = 'default')
	{
		$form = Fieldset::forge($name);
		if ($model_obj) $form->add_model($model_obj);

		$form->set_config('form_attributes', array('class' => 'form-horizontal'));
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Util_security::get_csrf()));

		return $form;
	}

	public static function check_is_api_request()
	{
		return strpos(Site_util::get_controller_name(), 'api') !== false;
	}

	public static function check_ids_in_model_objects($target_ids, $model_objects)
	{
		$ids = Util_db::get_ids_from_model_objects($model_objects);

		return Util_Array::array_in_array($target_ids, $ids);
	}
}
