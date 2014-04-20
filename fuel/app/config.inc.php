<?php

/**
 * Path to the project root directory.
 */
define('PRJ_BASEPATH', realpath(APPPATH.'../../').DIRECTORY_SEPARATOR);

// config.php の読み込み
require PRJ_BASEPATH.'config.php';

// BASE_URL
$PRJ_BASE_URL = sprintf('http://%s%s', PRJ_DOMAIN, PRJ_URI_PATH);
if (PRJ_ENVIRONMENT == 'STAGE') $PRJ_BASE_URL = str_replace('http://', 'http://stg.', $PRJ_BASE_URL);
define('PRJ_BASE_URL', $PRJ_BASE_URL);

// 公開ディレクトリ
if (!defined('PRJ_PUBLIC_DIR')) define('PRJ_PUBLIC_DIR', PRJ_BASEPATH.'public/');

// ファイルをアップロードするディレクトリ
if (!defined('PRJ_UPLOAD_DIRNAME')) define('PRJ_UPLOAD_DIRNAME', 'media');
if (!defined('PRJ_UPLOAD_DIR')) define('PRJ_UPLOAD_DIR', PRJ_PUBLIC_DIR.PRJ_UPLOAD_DIRNAME.'/');
//// Controller で Response する場合
//if (!defined('PRJ_UPLOAD_DIR')) define('PRJ_UPLOAD_DIR', APPPATH.'cache');

// アップロードするファイルの最大サイズ(単位: byte / 0 = no maximum) K/M/G 使用可能
$upload_max_filesize = _convert2bytes(ini_get('upload_max_filesize'));
if (!defined('PRJ_UPLOAD_MAX_FILESIZE')) define('PRJ_UPLOAD_MAX_FILESIZE', $upload_max_filesize);
if (PRJ_UPLOAD_MAX_FILESIZE > $upload_max_filesize) die('PRJ_UPLOAD_MAX_FILESIZE is over than php ini setting upload_max_filesize.');
if (PRJ_UPLOAD_MAX_FILESIZE > _convert2bytes(ini_get('post_max_size'))) die('PRJ_UPLOAD_MAX_FILESIZE is over than php ini setting post_max_size.');

// 一度にアップロードできるファイル数
if (!defined('PRJ_MAX_FILE_UPLOADS')) define('PRJ_MAX_FILE_UPLOADS', ini_get('max_file_uploads'));

// ImageMagick のパス(ImageMagick を使用する場合のみ)
if (!defined('PRJ_IMAGE_IMGMAGICK_PATH')) define('PRJ_IMAGE_IMGMAGICK_PATH', '');

// error level 設定
switch (PRJ_ENVIRONMENT)
{
	case 'DEVELOPMENT':
		define('PRJ_ERROR_REPORTING', E_ALL);
		define('PRJ_DISPLAY_ERRORS', 1);
		break;
	case 'TEST':
		define('PRJ_ERROR_REPORTING', E_ALL ^ E_NOTICE);
		define('PRJ_DISPLAY_ERRORS', 1);
		break;
	case 'STAGE':
	case 'PRODUCTION':
	default:
		define('PRJ_ERROR_REPORTING', 0);
		define('PRJ_DISPLAY_ERRORS', 0);
		break;
}
error_reporting(PRJ_ERROR_REPORTING);
ini_set('display_errors', PRJ_DISPLAY_ERRORS);

// public_flag 定義
//  非公開は 0, 他は値が大きいほど公開範囲が狭くなるように定義する
//  上記ルールに反する場合は js の is_expanded_public_range() を改修する必要がある。
define('PRJ_PUBLIC_FLAG_PRIVATE', 0);
define('PRJ_PUBLIC_FLAG_ALL',     1);
define('PRJ_PUBLIC_FLAG_MEMBER',  2);
//define('PRJ_PUBLIC_FLAG_FRIEND',  3);

// define default configs.
define('PRJ_DEFAULT_CONFIG_SETTING_FILE', PRJ_BASEPATH.'config.php.sample');
define('PRJ_DEFAULT_CONFIG_SETTING_CACHE', APPPATH.'cache/default_config_setting');
if (!file_exists(PRJ_DEFAULT_CONFIG_SETTING_FILE)) die('There is no config.php.sample.');

$default_configs = _get_default_configs();
foreach ($default_configs as $key => $value)
{
	if (defined($key)) continue;

	define($key, $value);
}



function _get_default_configs()
{
	$default_configs = array();
	if (!file_exists(PRJ_DEFAULT_CONFIG_SETTING_CACHE) || filemtime(PRJ_DEFAULT_CONFIG_SETTING_FILE) > filemtime(PRJ_DEFAULT_CONFIG_SETTING_CACHE))
	{
		$fp = fopen(PRJ_DEFAULT_CONFIG_SETTING_FILE, 'r');
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
		$default_configs = unserialize(file_get_contents(PRJ_DEFAULT_CONFIG_SETTING_CACHE));
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

	return file_put_contents(PRJ_DEFAULT_CONFIG_SETTING_CACHE, serialize($caches))
		&& chmod(PRJ_DEFAULT_CONFIG_SETTING_CACHE, 0777);
}

function _get_definition($strings)
{
	$pattern = "/^\s*define\(['\"]{1}([^'\"]+)['\"]{1},\s+(.+)\)/u";
	if (!preg_match($pattern, $strings, $matches)) return false;

	$key   = $matches[1];
	$value = $matches[2];

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
