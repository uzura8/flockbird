<?php

function conf($item, $file = 'site', $default = null, $replace_delimitter = null)
{
	if (!$file) $file = 'site';
	if ($replace_delimitter) $item = str_replace($replace_delimitter, '.', $item);

	return Config::get(sprintf('%s.%s', $file, $item), $default);
}

function is_enabled($module_name)
{
	if (!Module::loaded($module_name)) return false;
	if (!conf($module_name.'.isEnabled')) return false;

	return true;
}

function is_prod_env()
{
	return Site_Util::check_is_prod_env();
}

function is_dev_env()
{
	return Site_Util::check_is_dev_env();
}

function is_render_site_summary_at_client_side()
{
	static $is_render_site_summary_at_client_side;
	if (!is_null($is_render_site_summary_at_client_side)) return $is_render_site_summary_at_client_side;

	$conf = conf('view_params_default.post.url2link');
	$is_render_site_summary_at_client_side = $conf['isEnabled'] && ($conf['displaySummary']['renderAt'] == 'client');

	return $is_render_site_summary_at_client_side;
}

function get_enabled_modules_str($target_modules, $delimitter = '|')
{
	foreach ($target_modules as $key => $module)
	{
		if (!is_enabled($module)) unset($target_modules[$key]);
	}
	if (!$target_modules) return '';

	return implode($delimitter, $target_modules);
}

