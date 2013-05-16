<?php

function site_get_current_page_id($delimitter = '_')
{
	$items = array();
	if (isset(Request::main()->route->module)) $items[] = Request::main()->route->module;

	$controller = (isset(Request::main()->route->controller)) ?  Request::main()->route->controller : '';
	if ($controller) $items[] = Str::lower(preg_replace('/^[a-zA-Z0-9_]+\\\Controller_/', '', $controller));

	if (isset(Request::main()->route->action)) $items[] = Request::main()->route->action;

	return implode($delimitter, $items);
}

function site_title($title = '', $subtitle = '')
{
	$default_title = PRJ_SITE_DESCRIPTION.' '.PRJ_SITE_NAME;

	if (!$title && !$subtitle)
	{
		return $default_title;
	}
	if (!$subtitle) $subtitle = $default_title;
	if (!$title) $title = PRJ_SITE_NAME;

	return sprintf('%s [%s]', $title, $subtitle);
}

function site_header_keywords($keywords = '')
{
	if (!$keywords) return PRJ_HEADER_KEYWORDS_DEFAULT;

	if (is_array($keywords))
	{
		$keywords = implode(',', $keywords);
	}
	else
	{
		$keywords = trim($keywords, ',');
	}

	return $keywords.','.PRJ_HEADER_KEYWORDS_DEFAULT;
}

function site_get_screen_name($u)
{
	if (!$u) return Config::get('site.term.guest');

	return (!empty($u->name)) ? $u->name : 'メンバーID:'.$u->id;
}

function img($filename = '', $size = '50x50', $uri = '', $is_link2raw_file = false)
{
	if (empty($filename)) $filename = '';

	$identify = '';
	if (Site_util::check_filename_format($filename))
	{
		$identify = Util_string::get_exploded($filename);
	}

	$sizes = Config::get('site.upload_files.img.'.$identify.'.sizes');
	if (!$size || ($sizes && !in_array($size, $sizes))) $size = '50x50';
	list($width, $height) = explode('x', $size);

	$uri_basepath = Site_util::get_upload_path('img', $filename, true);
	$uri_path = sprintf('%s/%s/%s', $uri_basepath, $size, $filename);
	$file = sprintf('%s/%s', PRJ_PUBLIC_DIR, $uri_path);
	$option = array();

	if ($identify == 'm') $option = array('class' => 'profile_image');

	if (!$identify || !file_exists($file))
	{
		$option['alt'] = 'No image.';
		if (!empty($width)) $option['width'] = $width;

		$noimage_filename = 'noimage.gif';
		if ($identify) $noimage_filename = $identify.'_noimage.gif';
		$noimage_tag = Asset::img('site/'.$noimage_filename, $option);

		if ($uri) return Html::anchor($uri, $noimage_tag);

		return $noimage_tag;
	}

	$image_tag = Html::img($uri_path, $option);
	if ($uri) return Html::anchor($uri, $image_tag);

	if ($is_link2raw_file)
	{
		$image_uri = sprintf('%s/%s/%s', $uri_basepath, 'raw', $filename);
		return Html::anchor($image_uri, $image_tag);
	}

	return $image_tag;
}

function img_size($kind, $size)
{
	return Config::get(sprintf('site.upload_files.img.%s.sizes.%s', $kind, $size));
}

function site_get_time($mysql_datetime, $format = 'Y年n月j日 H:i', $is_normal_timestamp = false, $is_display_both = false)
{
	$time = $mysql_datetime;
	if (!$is_normal_timestamp) $time = strtotime($mysql_datetime);

	$normal_time = date($format, $time);
	$past_time   = sprintf('<span data-livestamp="%s"></span>', date(DATE_ISO8601, $time));

	$display = '';
	if ($is_display_both)
	{
		$display = sprintf('%s (%s)', $normal_time, $past_time);
	}
	else
	{
		if ($time < strtotime('-1 day'))
		{
			$display = $normal_time;
		}
		else
		{
			$display = $past_time;
		}
	}

	return $display;
}
