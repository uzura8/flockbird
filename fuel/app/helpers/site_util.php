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

function img($filename = '', $size = '50x50', $link_uri = '', $is_link2raw_file = false)
{
	$option = array();

	$is_noimage = false;
	if (empty($filename)) $is_noimage = true;
	if (!Site_util::check_filename_format($filename)) $is_noimage = true;

	$identifier = Util_string::get_exploded($filename);
	if ($identifier == 'm') $option['class'] = 'profile_image';

	if ($is_noimage)
	{
		$option['alt'] = 'No image.';

		$noimage_tag = Asset::img('site/noimage.gif', $option);
		if ($identifier)
		{
			$noimage_tag = Html::img(sprintf('%s/img/%s/%s_noimage.gif', PRJ_UPLOAD_DIRNAME, $identifier, $size), $option);
		}

		if ($link_uri) return Html::anchor($link_uri, $noimage_tag);

		return $noimage_tag;
	}

	$sizes = Config::get('site.upload_files.img.type.'.$identifier.'.sizes');
	if (!$size || ($sizes && !in_array($size, $sizes))) $size = '50x50';
	list($width, $height) = explode('x', $size);

	$uri_path = Site_util::get_uploaded_file_uri_path($filename, $size);

	$image_tag = Html::img($uri_path, $option);
	if ($link_uri) return Html::anchor($link_uri, $image_tag);

	if ($is_link2raw_file)
	{
		if (!Site_util::check_uploaded_file_exists($filename))
		{
			return $image_tag;
		}
		$uri_basepath = Site_util::get_upload_path('img', $filename, true);
		$image_uri = sprintf('%s/%s/%s', $uri_basepath, 'raw', $filename);

		return Html::anchor($image_uri, $image_tag);
	}

	return $image_tag;
}

function img_size($identifier, $size)
{
	return Config::get(sprintf('site.upload_files.img.type.%s.sizes.%s', $identifier, $size));
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
