<?php

function site_get_current_page_id($delimitter = '_')
{
	$items = array(
		Site_Util::get_module_name(),
		Site_Util::get_controller_name(),
		Site_Util::get_action_name(),
	);

	return implode($delimitter, $items);
}

function site_htmltag_include_js_module()
{
	$assets_uri = sprintf('modules/%s/site.js', Site_Util::get_module_name());
	$public_uri = 'assets/js/'.$assets_uri;
	if (!file_exists(PRJ_PUBLIC_DIR.'/'.$public_uri)) return '';

	return Asset::js($assets_uri);
}

function site_htmltag_include_js_action()
{
	$assets_uri = sprintf('modules/%s/%s_%s.js', Site_Util::get_module_name(), Site_Util::get_controller_name(), Site_Util::get_action_name());
	$public_uri = 'assets/js/'.$assets_uri;
	if (!file_exists(PRJ_PUBLIC_DIR.'/'.$public_uri)) return '';

	return Asset::js($assets_uri);
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
	if (!$u) return Config::get('term.guest');

	return (!empty($u->name)) ? $u->name : 'メンバーID:'.$u->id;
}

function img($filename = '', $size = '50x50', $link_uri = '', $is_link2raw_file = false)
{
	$option = array();

	$is_noimage = false;
	if (empty($filename)) $is_noimage = true;
	if (!Site_Upload::check_filename_format($filename)) $is_noimage = true;

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

	$uri_path = Site_Upload::get_uploaded_file_uri_path($filename, $size);

	$image_tag = Html::img($uri_path, $option);
	if ($link_uri) return Html::anchor($link_uri, $image_tag);

	if ($is_link2raw_file)
	{
		if (!Site_Upload::check_uploaded_file_exists($filename))
		{
			return $image_tag;
		}
		$uri_basepath = Site_Upload::get_upload_path('img', $filename, true);
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
		elseif ($time >= strtotime('-1 day') && $time < strtotime('-1 hour'))
		{
			$past_hours = Util_toolkit::get_past_time($time);
			$display = sprintf('約%d時間前', $past_hours);
		}
		else
		{
			$display = $past_time;
		}
	}

	return $display;
}

function strim($string, $width = 0, $trimmarker = '...', $is_html = true)
{
	if (!$width) return $string;

	$original_width = mb_strlen($string);

	if ($is_html) $string = Site_Util::html_entity_decode($string);
	$string = mb_strimwidth($string, 0, $width, $trimmarker);
	if ($is_html) $string = e($string);

	return $string;
}

function get_public_flag_label($public_flag, $view_icon_only = false, $is_return_string = false)
{
	switch ($public_flag)
	{
		case PRJ_PUBLIC_FLAG_ALL:
			$btn_color = ' btn-info';
			$icon      = '<i class="ls-icon-globe"></i> ';
			break;
		case PRJ_PUBLIC_FLAG_MEMBER:
			$btn_color = ' btn-success';
			$icon      = '<i class="ls-icon-group"></i> ';
			break;
		default :
			$btn_color = ' btn-danger';
			$icon      = '<i class="ls-icon-lock"></i> ';
			break;
	}
	$name = $view_icon_only ? '' : \Config::get('term.public_flag.options.'.$public_flag);

	if ($is_return_string) return $icon.$name;

	return array($name, $icon, $btn_color);
}
