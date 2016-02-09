<?php

/**
 * Path to the project root directory.
 */
define('FBD_BASEPATH', realpath(APPPATH.'../../').DIRECTORY_SEPARATOR);

// config.php の読み込み
require FBD_BASEPATH.'config.php';

// error level 設定
switch (FBD_ENVIRONMENT)
{
	case 'DEVELOPMENT':
		define('FBD_ERROR_REPORTING', E_ALL);
		define('FBD_DISPLAY_ERRORS', 1);
		break;
	case 'TEST':
		define('FBD_ERROR_REPORTING', E_ALL ^ E_NOTICE);
		define('FBD_DISPLAY_ERRORS', 1);
		break;
	case 'STAGING':
	case 'PRODUCTION':
	default:
		define('FBD_ERROR_REPORTING', 0);
		define('FBD_DISPLAY_ERRORS', 0);
		break;
}
error_reporting(FBD_ERROR_REPORTING);
ini_set('display_errors', FBD_DISPLAY_ERRORS);

// ファイルをアップロードするディレクトリ
if (!defined('FBD_UPLOAD_DIRNAME')) define('FBD_UPLOAD_DIRNAME', 'media');
if (!defined('FBD_UPLOAD_DIR')) define('FBD_UPLOAD_DIR', DOCROOT.FBD_UPLOAD_DIRNAME.'/');
//// Controller で Response する場合
//if (!defined('FBD_UPLOAD_DIR')) define('FBD_UPLOAD_DIR', APPPATH.'cache');

// アップロードするファイルの最大サイズ(単位: byte / 0 = no maximum) K/M/G 使用可能
$upload_max_filesize = _convert2bytes(ini_get('upload_max_filesize'));
if (!defined('FBD_UPLOAD_MAX_FILESIZE')) define('FBD_UPLOAD_MAX_FILESIZE', $upload_max_filesize);
if (FBD_UPLOAD_MAX_FILESIZE > $upload_max_filesize) die('FBD_UPLOAD_MAX_FILESIZE is over than php ini setting upload_max_filesize.');
if (FBD_UPLOAD_MAX_FILESIZE > _convert2bytes(ini_get('post_max_size'))) die('FBD_UPLOAD_MAX_FILESIZE is over than php ini setting post_max_size.');

// 一度にアップロードできるファイル数
if (!defined('FBD_MAX_FILE_UPLOADS')) define('FBD_MAX_FILE_UPLOADS', ini_get('max_file_uploads'));

// ImageMagick のパス(ImageMagick を使用する場合のみ)
if (!defined('FBD_IMAGE_IMGMAGICK_PATH')) define('FBD_IMAGE_IMGMAGICK_PATH', '');

// profiling 設定 ON 時はプロファイラにクエリ情報を追加する
if (FBD_PROFILING)
{
	$env_key = strtolower(FBD_ENVIRONMENT);
	foreach ($GLOBALS['_FBD_DSN'][$env_key] as $db => $config)
	{
		if (isset($GLOBALS['_FBD_DSN'][$env_key][$db]['profiling'])) continue;
		$GLOBALS['_FBD_DSN'][$env_key][$db]['profiling'] = true;
	}
}

if (!defined('FBD_DOMAIN') && !empty($_SERVER['HTTP_HOST'])) define('FBD_DOMAIN', $_SERVER['HTTP_HOST']);
if (!defined('FBD_URI_PATH') && !empty($_SERVER['FBD_URI_PATH'])) define('FBD_URI_PATH', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));

// set const is task
if (!defined('IS_TASK')) define('IS_TASK', false);

// define default configs.
_set_default_configs();

// BASE_URL
$protocol = (FBD_SSL_MODE == 'ALL') ? 'https' : 'http';
$prefix = '';
//if (FBD_ENVIRONMENT == 'STAGING') $prefix = 'stg.';
//if (FBD_ENVIRONMENT == 'TEST') $prefix = 'test.';
define('FBD_BASE_URL', sprintf('%s://%s%s%s', $protocol, $prefix, FBD_DOMAIN, FBD_URI_PATH));

// public_flag 定義
//  非公開は 0, 他は値が大きいほど公開範囲が狭くなるように定義する
//  上記ルールに反する場合は js の is_expanded_public_range() を改修する必要がある。
define('FBD_PUBLIC_FLAG_PRIVATE', 0);
define('FBD_PUBLIC_FLAG_ALL',     1);
define('FBD_PUBLIC_FLAG_MEMBER',  2);
define('FBD_PUBLIC_FLAG_FRIEND',  3);


function _set_default_configs()
{
	define('FBD_DEFAULT_CONFIG_SETTING_FILE', FBD_BASEPATH.'config.php.sample');
	define('FBD_DEFAULT_CONFIG_SETTING_CACHE', APPPATH.'cache/default_config_setting');
	if (!file_exists(FBD_DEFAULT_CONFIG_SETTING_FILE)) die('There is no config.php.sample.');

	$default_configs = _get_default_configs();
	foreach ($default_configs as $key => $value)
	{
		if (defined($key)) continue;

		define($key, $value);
	}
	_set_default_globals();
}

function _set_default_globals()
{
	if (!isset($GLOBALS['_FBD_ADDITIONAL_MODULES'])) $GLOBALS['_FBD_ADDITIONAL_MODULES'] = array();
}

function _get_default_configs()
{
	$default_configs = array();
	if (!file_exists(FBD_DEFAULT_CONFIG_SETTING_CACHE) || filemtime(FBD_DEFAULT_CONFIG_SETTING_FILE) > filemtime(FBD_DEFAULT_CONFIG_SETTING_CACHE))
	{
		$fp = fopen(FBD_DEFAULT_CONFIG_SETTING_FILE, 'r');
		if (!$fp) die('Unable to open config.php.sample.');

		while(!feof($fp))
		{
			$line = trim(fgets($fp));
			if (!$result = _get_definition($line)) continue;

			list($key, $value) = $result;
			$default_configs[$key] = $value;
		}
		fclose($fp);
		_make_cache($default_configs);
	}
	else
	{
		$default_configs = unserialize(file_get_contents(FBD_DEFAULT_CONFIG_SETTING_CACHE));
	}

	return $default_configs;
}

function _make_cache($list)
{
	if (empty($list)) return false;

	$caches = array();
	foreach ($list as $key => $value)
	{
		if (is_string($value)) $value = addslashes($value);
		$caches[$key] = $value;
	}
	unset($list);

	return file_put_contents(FBD_DEFAULT_CONFIG_SETTING_CACHE, serialize($caches))
		&& chmod(FBD_DEFAULT_CONFIG_SETTING_CACHE, 0777);
}

function _get_definition($strings)
{
	$pattern = "#^(//)?\s*define\(['\"]{1}([^'\"]+)['\"]{1},\s+(.+)\)#u";
	if (!preg_match($pattern, $strings, $matches)) return false;

	$key   = $matches[2];
	$value = $matches[3];

	if ('true'  === strtolower($value))  return array($key, true);
	if ('false' === strtolower($value))  return array($key, false);

	if (preg_match("/^['\"]{1}(.*)['\"]{1}$/u", $value, $matches)) return array($key, (string)$matches[1]);

	return array($key, (int)$value);
}

function _convert2bytes($val)
{
	$val  = trim($val);
	$last = strtolower($val[strlen($val) - 1]);
	switch($last) {
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}

	return $val;
}
