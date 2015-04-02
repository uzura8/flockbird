<?php
class Site_Config
{
	public static function merge_module_configs($config, $config_name)
	{
		$modules = Site_Util::get_active_modules();
		foreach ($modules as $module => $path)
		{
			Config::load($module.'::'.$config_name, $module.'_'.$config_name);
			if (!$module_config = Config::get($module.'_'.$config_name)) continue;

			$config = Arr::merge($config, $module_config);
		}

		return $config;
	}

	public static function merge_db_configs($configs, $table)
	{
		$class = 'Model_'.Inflector::camelize($table);
		$db_configs = $class::get_valueas_assoc();
		foreach ($db_configs as $name => $value)
		{
			$key = str_replace('_', '.', $name);
			Arr::set($configs, $key, $value);
		}

		return $configs;
	}

	public static function setup_configs_template($configs)
	{
		$configs = static::merge_db_configs_template($configs);
		$configs = static::setup_configs_template_body($configs);

		return $configs;
	}

	public static function merge_db_configs_template($configs)
	{
		if ($db_configs = Model_Template::query()->get())
		{
			foreach ($db_configs as $db_config)
			{
				$key = str_replace('_', '.', $db_config->name);
				$values = Arr::get($configs, $key);
				if ($db_config->format) $values['format'] = $db_config->format;
				if ($db_config->title) $values['title'] = $db_config->title;
				if ($db_config->body) $values['body'] = $db_config->body;
				if (!$values) continue;

				Arr::set($configs, $key, $values);
			}
		}

		return $configs;
	}

	public static function setup_configs_template_body($configs)
	{
		foreach ($configs as $type => $types)
		{
			foreach ($types as $module => $modules)
			{
				foreach ($modules as $item_key => $items)
				{
					if (!isset($items['body'])) continue;
					if (!is_array($items['body'])) continue;

					if (!empty($items['body']['default']['file']))
					{
						$ext = (!empty($items['format'])) ? $items['format'] : 'php';
						$body = file_get_contents(sprintf('%sviews/%s.%s', APPPATH, $items['body']['default']['file'], $ext));
					}
					elseif (!empty($items['body']['default']['value']))
					{
						$body = $items['body']['default']['value'];
					}
					else
					{
						continue;
					}
					$key = implode('.', array($type, $module, $item_key, 'body'));
					Arr::set($configs, $key, $body);
				}
			}
		}

		return $configs;
	}

	/**
	 * module の load 状態による設定値の修正
	 *
	 * @access  public
	 * @return  null
	 */
	public static function regulate_configs_for_module_loaded()
	{
		if (Config::get('site.upload.types.img.types.m.save_as_album_image') && !is_enabled('album'))
		{
			Config::set('site.upload.types.img.types.m.save_as_album_image', false);
		}
	}
}
