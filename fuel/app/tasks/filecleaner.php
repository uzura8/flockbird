<?php
namespace Fuel\Tasks;

/**
 * Task FileCleaner
 */

class FileCleaner
{
	private static $absolute_execute = false;
	private static $all_delete = false;

	public function __construct()
	{
		self::$absolute_execute = \Cli::option('absolute_execute', false);
		self::$all_delete = \Cli::option('all_delete', false);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r filecleaner
	 *
	 * @return string
	 */
	public static function run($exec_mode = null)
	{
		$messages = array();
		try
		{
			$messages[] = self::clean_img_tmp();
			$messages[] = self::clean_img();
			$messages[] = self::clean_file_tmp();
			$messages[] = self::clean_file();
			$messages[] = self::clean_cache();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('File_cleaner error: %s', $e->getMessage()), false);
		}

		return implode(PHP_EOL, $messages);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r filecleaner:clean_img_tmp
	 *
	 * @return string
	 */
	public static function clean_img_tmp()
	{
		try
		{
			$message = self::execute_clean_file('img', true);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('%s error: %s', __FUNCTION__, $e->getMessage()), false);
		}

		return \Util_Task::output_result_message(true, __FUNCTION__, $message);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r filecleaner:clean_img
	 *
	 * @return string
	 */
	public static function clean_img()
	{
		try
		{
			$message = self::execute_clean_file('img', false);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('%s error: %s', __FUNCTION__, $e->getMessage()), false);
		}

		return \Util_Task::output_result_message(true, __FUNCTION__, $message);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r filecleaner:clean_file_tmp
	 *
	 * @return string
	 */
	public static function clean_file_tmp()
	{
		try
		{
			$message = self::execute_clean_file('file', true);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('%s error: %s', __FUNCTION__, $e->getMessage()), false);
		}

		return \Util_Task::output_result_message(true, __FUNCTION__, $message);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r filecleaner:clean_file
	 *
	 * @return string
	 */
	public static function clean_file()
	{
		try
		{
			$message = self::execute_clean_file('file', false);
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('%s error: %s', __FUNCTION__, $e->getMessage()), false);
		}

		return \Util_Task::output_result_message(true, __FUNCTION__, $message);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r filecleaner:clean_cache
	 *
	 * @return string
	 */
	public static function clean_cache()
	{
		try
		{
			\Cache::delete_all();
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('%s error: %s', __FUNCTION__, $e->getMessage()), false);
		}

		return \Util_Task::output_result_message(true, __FUNCTION__, 'Delete all caches.');
	}

	private static function check_file_exists_record($file_path, $is_tmp = false)
	{
		if (self::$all_delete) return false;

		$model = $is_tmp ? '\Model_FileTmp' : '\Model_File';
		if (!$filename = \Site_Upload::get_filename_from_file_path($file_path)) return false;

		return (bool)$model::get4name($filename);
	}

	private static function execute_clean_file($file_type, $is_tmp = false)
	{
		if (!self::$absolute_execute && !\Site_Util::check_is_dev_env())
		{
			throw new \FuelException('This task is not work at prod env.');
		}

		$raw_file_dir_path = \Site_Upload::get_uploaded_path('raw', $file_type, $is_tmp);
		$cache_file_dir_path = $is_tmp ? null : PRJ_PUBLIC_DIR.conf('upload.types.'.$file_type.'.root_path.cache_dir');
		if (!file_exists($raw_file_dir_path) && ($cache_file_dir_path && !file_exists($cache_file_dir_path)))
		{
			return "File directry '".$raw_file_dir_path."' not exists.";
		}
		$file_paths = \Util_file::get_file_recursive($raw_file_dir_path);
		if ($cache_file_dir_path) $file_paths = array_merge($file_paths, \Util_file::get_file_recursive($cache_file_dir_path));
		if (!$file_paths) return sprintf("No files at '%s'", $raw_file_dir_path);

		$i = 0;
		foreach ($file_paths as $file_path)
		{
			if (self::check_file_exists_record($file_path, $is_tmp)) continue;

			\Util_file::remove($file_path);
			$i++;
		}
		$subject = $file_type;
		if ($is_tmp) $subject .= '_tmp';

		return sprintf('%d %s removed.', $i, $subject);
	}
}

/* End of file tasks/filetmp.php */
