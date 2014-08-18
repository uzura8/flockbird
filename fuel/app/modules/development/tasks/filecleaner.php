<?php
namespace Fuel\Tasks;

/**
 * Task FileCleaner
 */

class FileCleaner
{
	private static $not_check_record_exists = false;

	public function __construct()
	{
		if (!\Site_Util::check_is_develop_env())
		{
			throw new \FuelException('This task is not work at prod env.');
		}
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r development::filecleaner
	 *
	 * @return string
	 */
	public static function run($exec_mode = null)
	{
		if ($exec_mode == 'absolue_delete') self::$not_check_record_exists = true;

		$messages = array();
		try
		{
			$messages[] = self::clean_img_tmp();
			$messages[] = self::clean_img();
			$messages[] = self::clean_file_tmp();
			$messages[] = self::clean_file();
		}
		catch(\FuelException $e)
		{
			$messages[] = 'Error: '.$e->getMessage();
		}

		return implode(PHP_EOL, $messages);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r development::filecleaner:clean_img_tmp
	 *
	 * @return string
	 */
	public static function clean_img_tmp()
	{
		return self::execute_clean_file('img', true);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r development::filecleaner:clean_img
	 *
	 * @return string
	 */
	public static function clean_img()
	{
		return self::execute_clean_file('img', false);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r development::filecleaner:clean_file_tmp
	 *
	 * @return string
	 */
	public static function clean_file_tmp()
	{
		return self::execute_clean_file('file', true);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r development::filecleaner:clean_file
	 *
	 * @return string
	 */
	public static function clean_file()
	{
		return self::execute_clean_file('file', false);
	}

	private static function check_file_exists_record($file_path, $is_tmp = false)
	{
		if (self::$not_check_record_exists) return false;

		$model = $is_tmp ? '\Model_FileTmp' : '\Model_File';
		$file_info = \File::file_info($file_path);

		return (bool)$model::get4name($file_info['basename']);
	}

	private static function execute_clean_file($file_type, $is_tmp = false)
	{
		$raw_file_dir_path = conf(sprintf('upload.types.%s%s.raw_file_path', $file_type, $is_tmp ? '.tmp' : ''));
		if (!file_exists($raw_file_dir_path))
		{
			return "File directry '".$raw_file_dir_path."' not exists.";
		}
		if (!$file_paths = \Util_file::get_file_recursive($raw_file_dir_path))
		{
			return sprintf("No files at '%s'", $raw_file_dir_path);
		}

		$i = 0;
		foreach ($file_paths as $file_path)
		{
			if (self::check_file_exists_record($file_path, $is_tmp)) continue;

			\Util_file::remove($file_path);
			$i++;
		}

		return $i ? $i.' file_tmps removed.' : 'All files exist record.';
	}
}

/* End of file tasks/filetmp.php */
