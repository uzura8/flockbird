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

	public static function get_upload_basepath($type, $key, $id)
	{
		$allow_types = array('img', 'movie');
		if (!in_array($type, $allow_types)) throw new Exception($type.' is not accepted.');

		$middle_dir_name = substr($id, -1);

		return sprintf('%s/%s/%s/%s', $type, $key, $middle_dir_name, $id);
	}

	public static function get_upload_basedir($type, $key, $id)
	{
		return sprintf('%s/%s', PRJ_UPLOAD_DIR, self::get_upload_basepath($type, $key, $id));
	}

	public static function create_upload_dirs($type, $key, $id)
	{
		try
		{
			$upload_base_dir = self::get_upload_basedir($type, $key, $id);
			Util_file::make_dir($upload_base_dir);
			$image_configs = Config::get('site.upload_files.'.$type);
			foreach ($image_configs as $category => $values)
			{
				$category_path = sprintf('%s/%s', $upload_base_dir, $category);
				if (!file_exists($category_path)) Util_file::make_dir($category_path);
				foreach ($values as $property => $dirs)
				{
					if ($property != 'sizes') continue;

					foreach ($dirs as $dir)
					{
						$path = sprintf('%s/%s', $category_path, $dir);
						if (!file_exists($path)) Util_file::make_dir($path);
					}
				}
			}
		}
		catch(Exception $e)
		{
			return false;
		}

		return $upload_base_dir;
	}
}
