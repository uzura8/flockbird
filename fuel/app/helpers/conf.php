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

function is_enabled_share($service_name, $type = null)
{
	$configs = conf('site.common.shareButton', 'page');
	if (!Arr::get($configs, 'isEnabled')) return false;
	if (!in_array($service_name, array('twitter', 'facebook', 'line', 'google'))) return false;

	if ($service_name == 'facebook')
	{
		if (!$type) return false;
		return (bool)Arr::get($configs, sprintf('%s.%s.isEnabled', $service_name, $type));
	}

	return (bool)Arr::get($configs, sprintf('%s.isEnabled', $service_name));
}

function view_params($key, $content, $page_type = null, $default = null, $is_admin = false)
{
	if (!$page_type) $page_type = 'list';

	return conf(sprintf('%s.viewParams.%s.%s.%s', $is_admin ? 'admin' : 'site', $content, $page_type, $key), 'page', $default);
}

function is_enabled_public_flag($public_flag_key)
{
	return in_array(Site_Util::get_public_flag_value4key($public_flag_key), conf('public_flag.enabled'));
}

function is_admin()
{
	return defined('IS_ADMIN') && IS_ADMIN;
}

function is_enabled_i18n()
{
	return conf('isEnabled', 'i18n');
}

function get_default_lang()
{
	return Config::get('i18n.defaultLang', 'ja');
}

function get_lang($is_check_member_lang_setting = true)
{
	return Site_Lang::get_lang($is_check_member_lang_setting);
}

function is_lang_ja()
{
	if (!is_enabled_i18n()) return true;

	return Lang::get_lang() == 'ja';
}

