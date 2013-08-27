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

function site_get_form_id($delimitter = '_')
{
	$items = array(
		'form',
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

function img($file = array(), $size = '', $link_uri = '', $is_link2raw_file = false, $alt = '')
{
	$option = array();
	$is_raw = $size == 'raw';
	list($filepath, $filename) = Site_Upload::split_file_object2vars($file);

	$file_cate  = Util_string::get_exploded($filepath, 0, '/');
	if ($file_cate == 'm') $option['class'] = 'profile_image';

	$is_noimage = false;
	if (empty($filename)) $is_noimage = true;
	if (!$is_noimage && !Site_Upload::check_filepath_format($filepath)) $is_noimage = true;
	if ($is_noimage)
	{
		$option['alt'] = $alt ?: 'No image.';
		$noimage_filename  = Config::get('site.upload.types.img.noimage_filename');
		$noimage_tag = Asset::img('site/'.$noimage_filename, $option);
		if ($file_cate)
		{
			if ($is_raw)
			{
				$noimage_file_root_path = sprintf('assets/site/%s_%s', $file_cate, $noimage_filename);
			}
			else
			{
				$noimage_file_root_path = sprintf('%s/img/%s/%s/all/noimage.gif', PRJ_UPLOAD_DIRNAME, $size, $file_cate);
			}
			$noimage_tag = Html::img($noimage_file_root_path, $option);
		}
		if ($link_uri) return Html::anchor($link_uri, $noimage_tag);

		return $noimage_tag;
	}

	$uri_path_raw = sprintf('%s/img/raw/%s%s', PRJ_UPLOAD_DIRNAME, $filepath, $filename);
	$uri_path = $is_raw ? $uri_path_raw : sprintf('%s/img/%s/%s%s', PRJ_UPLOAD_DIRNAME, $size, $filepath, $filename);
	if ($alt) $option['alt'] = $alt;
	$image_tag = Html::img($uri_path, $option);

	if ($link_uri) return Html::anchor($link_uri, $image_tag);
	if ($is_link2raw_file) return Html::anchor($uri_path_raw, $image_tag);

	return $image_tag;
}

function img_size($file_cate, $size)
{
	return Config::get(sprintf('site.upload.types.img.types.%s.sizes.%s', $file_cate, $size));
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
