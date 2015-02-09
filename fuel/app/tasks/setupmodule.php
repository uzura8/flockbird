<?php
namespace Fuel\Tasks;

/**
 * Task SetupModule
 */

class SetupModule
{
	private static $absolute_execute = false;

	public function __construct($args = null)
	{
		self::$absolute_execute = \Cli::option('absolute_execute', false);
	}

	/**
	 * Usage (from command line):
	 *
	 * php oil r setupmodule
	 *
	 * @return string
	 */
	public static function run($target_module = null)
	{
		$is_execued = false;
		try
		{
			$modules = $target_module ? (array)$target_module : \Module::loaded();
			if (!$modules)
			{
				return;
			}

			foreach ($modules as $module => $module_dir_path)
			{
				if ($messages = self::setup_assets($module, $module_dir_path))
				{
					foreach ($messages as $message) echo $message.PHP_EOL;
					$is_execued = true;
				}
			}
		}
		catch(\FuelException $e)
		{
			return \Util_Task::output_message(sprintf('Setup modules error: %s', $e->getMessage()), false);
		}

		return $is_execued ? \Util_Task::output_result_message(true, 'setup modules') : '';
	}

	private static function setup_assets($module, $module_dir_path)
	{
		$public_dir_path = PRJ_BASEPATH.'public/'	;
		if (!file_exists($public_dir_path)) throw new \FuelException('Not exists public dir.');
		chdir($public_dir_path.'modules/');

		$messages = array();

		$module_dir_middle_path = sprintf('modules/%s/', $module);
		$module_public_dir_path = sprintf(APPPATH.$module_dir_middle_path.'public/');
		if (file_exists($module_public_dir_path) && !file_exists($public_dir_path.$module_dir_middle_path))
		{
			$target_relative_path = '../../fuel/app/'.$module_dir_middle_path.'public';
			symlink($target_relative_path, $module);
			$messages[] = \Util_Task::output_message(sprintf("Add symlink '%s' to 'public/modules/%s'.", $target_relative_path, $module));
		}

		$assets_base_dir_path = $public_dir_path.'assets/'	;
		$assets_dirs = array('css', 'img', 'js');
		foreach ($assets_dirs as $asset_type)
		{
			if (!file_exists($assets_base_dir_path.$asset_type)) continue;
			if (!chdir($assets_base_dir_path.$asset_type.'/modules')) continue;
			if (file_exists(sprintf('%s%s/modules/%s', $assets_base_dir_path, $asset_type, $module))) continue;

			$module_assets_dir_path_suffix = sprintf('%sassets/%s', $module_dir_middle_path, $asset_type);
			if (!file_exists(APPPATH.$module_assets_dir_path_suffix)) continue;

			$target_relative_path = '../../../../fuel/app/'.$module_assets_dir_path_suffix;
			symlink($target_relative_path, $module);
			$messages[] = \Util_Task::output_message(sprintf("Add symlink '%s' to 'public/assets/%s/modules/%s'.", $target_relative_path, $asset_type, $module));
		}

		return $messages;
	}
}
/* End of file tasks/setup.php */
