<?php
/**
 * FuelPHP LessCSS package implementation.
 */

class Asset_Instance extends \Less\Asset_Instance
{
	/**
	 * Less
	 *
	 * Compile a Less file and load it as a CSS asset.
	 *
	 * @param array|string $stylesheets The file name, or an array files.
	 * @param array $attr An array of extra attributes
	 * @param string $group The asset group name
	 * @param bool $raw Whether to return the raw file or not
	 * @param bool $is_force_compile Compile by force regardless of enviroment
	 * @return object|string Rendered asset or current instance when adding to group
	 * @throws \Exception
	 */
	public function less($stylesheets = array(), $attr = array(), $group = null, $raw = false, $is_force_compile = false)
	{
		if ( ! is_array($stylesheets))
		{
			$stylesheets = array($stylesheets);
		}
		
		foreach($stylesheets as &$lessfile)
		{
			$source_less = static::get_source_less($lessfile);
			if( ! is_file($source_less))
			{
				throw new \Exception('Could not find less source file: '.$source_less);
			}

			// Change the name for loading with Asset::css
			$lessfile = str_replace('.'.pathinfo($lessfile, PATHINFO_EXTENSION), '', $lessfile).'.css';
			
			// Full path to css compiled file
			$compiled_css = \Config::get('asset.less_output_dir').$lessfile;

			// Compile only if we detect modifications in less file
			if (static::_detect_modifications($source_less, $compiled_css, $is_force_compile))
			{
				// Get the driver name once
				static $driver = null;
				if (!$driver)
				{
					$driver = \Config::get('asset.less_compiler');
				}

				// Then compile the file
				$driver::compile($source_less, $compiled_css);
			}
		}
		
		return static::css($stylesheets, $attr, $group, $raw);
	}

	/**
	 * Detect modifications in a Less file recursively
	 *
	 * @param string $source_less
	 * @param string $compiled_css
	 * @param bool $is_force_compile Execute by force regardless of enviroment
	 * @return bool
	 */
	protected static function _detect_modifications($source_less, $compiled_css, $is_force_compile = false)
	{
		// Avoid overhead in production. When modified the less content,
		// the developer should also send the .css compiled file to the
		// production environment.
		if (!$is_force_compile && \Fuel::$env !== \Fuel::DEVELOPMENT)
		{
			return false;
		}

		// If the file doesn't exist or the main less file is newer than the css, it's modified
		if ( ! is_file($compiled_css) or filemtime($source_less) > filemtime($compiled_css))
		{
			return true;
		}

		// Add the first file to the imports array
		$imports = array($source_less);

		while($element = each($imports))
		{
			$file = $element[1];
			$base_dir = dirname($file);

			if($css_imports = static::_get_imports($file))
			{
				// Put the full path of the import files
				foreach ($css_imports as $import)
				{
					$imports[] = $base_dir . DS . trim($import, '/');
				}
			}
		}

		// Remove the first element - which is the main less file (because we already checked it)
		unset($imports[0]);

		// Remove duplicate values (if any)
		$imports = array_unique($imports);

		// Iterate recursively all files and seek for any file newer than the compiled css
		foreach ($imports as $file)
		{
			if (filemtime($file) > filemtime($compiled_css))
			{
				return true;
			}
		}

		return false;
	}

	protected function get_source_less($lessfile_name)
	{
		$source_file_path = '';
		$modules = Module::loaded();
		foreach ($modules as $module => $path)
		{
			$file_path = sprintf('%sassets/less/%s', $path, $lessfile_name);
			if (!file_exists($file_path)) continue;
			$source_file_path = $file_path;
		}

		return $source_file_path ?: \Config::get('asset.less_source_dir').$lessfile_name;
	}
}
