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

function site_get_screen_name($current_user)
{
	if (!$current_user) return Config::get('site.term.guest');

	return (!empty($current_user->name)) ? $current_user->name : 'メンバーID:'.$current_user->id;
}

function site_profile_image($member_id, $member_image, $size = '50x50', $uri = '', $is_link2raw_file = false)
{
	$sizes = Config::get('site.upload_files.img.profile.sizes');
	if (!in_array($size, $sizes)) $size = '50x50';
	list($width, $height) = explode('x', $size);

	$config = array();
	$config['alt'] = 'No image.';
	$config['width'] = $width;
	$config['class'] = 'profile_image';
	$noimage_tag = Html::img('upload/img/member/noimage.gif', $config);
	$basedir  = Site_util::get_upload_basedir('img', 'member', $member_id);
	$basepath = Site_util::get_upload_basepath('img', 'member', $member_id);

	$file = sprintf('%s/profile/%s/%s', $basedir, $size, $member_image);
	if (empty($member_image) || !file_exists($file))
	{
		if (!empty($uri)) return Html::anchor($uri, $noimage_tag);

		return $noimage_tag;
	}

	$image_uri = sprintf('upload/%s/profile/%s/%s', $basepath, $size, $member_image);
	$image_tag = Html::img($image_uri, array('class' => 'profile_image'));

	if (!empty($uri)) return Html::anchor($uri, $image_tag);
	if ($is_link2raw_file)
	{
		$image_uri = sprintf('upload/%s/profile/%s/%s', $basepath, 'raw', $member_image);
		return Html::anchor($image_uri, $image_tag);
	}

	return $image_tag;
}

function site_get_time($mysql_datetime, $format = 'Y年n月j日 H:i', $is_normal_timestamp = false, $is_display_both = false)
{
	$time = $mysql_datetime;
	if (!$is_normal_timestamp) $time = strtotime($mysql_datetime);

	$normal_time = date($format, $time);
	$past_time   = Date::time_ago($time);

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
