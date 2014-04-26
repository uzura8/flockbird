<?php

function site_get_current_page_id($delimitter = '_')
{
	$items = array();
	if ($module = Site_Util::get_module_name()) $items[] = $module;
	$items[] = Site_Util::get_controller_name();
	$items[] = Site_Util::get_action_name();

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
	$assets_uri = sprintf('site/modules/%s/site.js', Site_Util::get_module_name());
	$public_uri = 'assets/js/'.$assets_uri;
	if (!file_exists(PRJ_PUBLIC_DIR.'/'.$public_uri)) return '';

	return Asset::js($assets_uri);
}

function site_htmltag_include_js_action()
{
	$module = Site_Util::get_module_name();
	$assets_uri = sprintf('site/%s%s_%s.js', $module ? sprintf('modules/%s/', $module) : '', Site_Util::get_controller_name(), Site_Util::get_action_name());
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

	if (!empty($u->name)) return $u->name;
	if (!empty($u->username)) return $u->username;

	return 'ID:'.$u->id;
}

function auth_check()
{
	return defined('IS_AUTH') && IS_AUTH;
}

function conf($item, $file = null, $default = null, $replace_delimitter = null)
{
	if (!$file) $file = 'site';
	if ($replace_delimitter) $item = str_replace($replace_delimitter, '.', $item);

	return Config::get(sprintf('%s.%s', $file, $item), $default);
}

function term()
{
	$keys = func_get_args();
	if (count($keys) === 1 && is_array($keys[0])) $keys = $keys[0];

	$return = '';
	foreach ($keys as $key)
	{
		$return .= Config::get('term.'.$key, $key);
	}

	return $return;	
}

function symbol($key)
{
	return Config::get('term.symbol.'.$key);	
}

function symbol_bool($bool)
{
	return $bool ? symbol('bool.true') : symbol('bool.false');
}

function img($file = array(), $size = '', $link_uri = '', $is_link2raw_file = false, $alt = '', $is_profile_image = false, $is_img_responsive = false, $anchor_attrs = array())
{
	$option = array('class' => '');
	if ($is_img_responsive) $option['class'] = 'img-responsive';
	$is_raw = $size == 'raw';
	list($filepath, $filename) = Site_Upload::split_file_object2vars($file);

	$file_cate  = Util_string::get_exploded($filepath, 0, '/');
	if ($is_profile_image)
	{
		if (!empty($option['class'])) $option['class'] .= ' ';
		$option['class'] .= 'profile_image';
	}

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
		if ($link_uri) return Html::anchor($link_uri, $noimage_tag, $anchor_attrs);

		return $noimage_tag;
	}

	$uri_path_raw = sprintf('%s/img/raw/%s%s', PRJ_UPLOAD_DIRNAME, $filepath, $filename);
	$uri_path = $is_raw ? $uri_path_raw : sprintf('%s/img/%s/%s%s', PRJ_UPLOAD_DIRNAME, $size, $filepath, $filename);
	if ($alt) $option['alt'] = $alt;
	$image_tag = Html::img($uri_path, $option);

	if ($link_uri) return Html::anchor($link_uri, $image_tag, $anchor_attrs);
	if ($is_link2raw_file)
	{
		$anchor_attrs['target'] = '_blank';
		return Html::anchor($uri_path_raw, $image_tag, $anchor_attrs);
	}

	return $image_tag;
}

function img_size($file_cate, $size, $additional_table = '')
{
	if ($additional_table) return Config::get(sprintf('site.upload.types.img.types.%s.additional_sizes.%s.%s', $file_cate, $additional_table, $size));
	return Config::get(sprintf('site.upload.types.img.types.%s.sizes.%s', $file_cate, $size));
}

function site_get_time($mysql_datetime, $display_type = 'relative', $format = 'Y年n月j日 H:i', $display_both_length = '+7 day', $is_normal_timestamp = false)
{
	$accept_display_types = array('relative', 'normal', 'both');
	if (!in_array($display_type, $accept_display_types)) throw new InvalidArgumentException('Second parameter is invalid.');

	$time = $mysql_datetime;
	if (!$is_normal_timestamp) $time = strtotime($mysql_datetime);

	$normal_time = date($format, $time);
	if ($display_type == 'normal') return $normal_time;

	$past_time = sprintf('<span data-livestamp="%s"></span>', date(DATE_ISO8601, $time));

	$display = '';
	if ($display_type == 'both'
		&& (is_null($display_both_length) || !is_null($display_both_length) && (time() < strtotime($normal_time.' '.$display_both_length))))
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

function truncate_lines($body, $line, $read_more_uri = '', $is_convert_nl2br = true, $trimmarker = '...')
{
	list($body, $is_truncated) = Util_string::truncate_lines($body, $line, $trimmarker, Config::get('encoding'));

	return render('_parts/truncated_body', array(
		'body' => $body,
		'is_truncated' => $is_truncated,
		'read_more_uri' => $read_more_uri,
		'is_convert_nl2br' => $is_convert_nl2br,
	));
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

function get_csrf_query_str($delimitter = '?')
{
	return sprintf('%s%s=%s', $delimitter, Config::get('security.csrf_token_key'), Util_security::get_csrf());
}

function conv_data_atter($list = array(), $is_html = false)
{
	$output = $is_html ? '' : array();
	foreach ($list as $key => $value)
	{
		if ($is_html)
		{
			$output .= sprintf(' data-%s="%s"', $key, $value);
		}
		else
		{
			$output['data-'.$key] = $value;
		}
	}

	return $output;
}

function check_public_flag($public_flag, $access_from)
{
	switch ($public_flag)
	{
		case PRJ_PUBLIC_FLAG_PRIVATE:
			if ($access_from == 'self') return true;
			break;
		//case PRJ_PUBLIC_FLAG_FRIEND:
		//	if (in_array($access_from, array('self', 'friend'))) return true;
		//	break;
		case PRJ_PUBLIC_FLAG_MEMBER:
			if (in_array($access_from, array('self', 'friend', 'member'))) return true;
			break;
		default :
			return true;
			break;
	}

	return false;
}

function check_public_flags($public_flags, $access_from, $strict_cond = true)
{
	foreach ($public_flags as $public_flag)
	{
		if (!$strict_cond && check_public_flag($public_flag, $access_from)) return true;
		if ($strict_cond && !check_public_flag($public_flag, $access_from)) return false;
	}

	return $strict_cond ? true : false;
}

function check_display_type($contents_display_type, $page_display_type_str = 'detail')
{
	$page_display_type = conf('member.profile.display_type.'.$page_display_type_str, 0);

	return $contents_display_type >= $page_display_type;
}

function profile_value(Model_MemberProfile $member_profile)
{
	switch ($member_profile->profile->form_type)
	{
		case 'checkbox':
		case 'select':
		case 'radio':
			return $member_profile->profile_option->label;
			break;
		case 'textarea':
			return nl2br($member_profile->value);
			break;
	}

	return $member_profile->value;
}

function is_enabled($module_name)
{
	if (!Module::loaded($module_name)) return false;
	if (!conf($module_name.'.isEnabled')) return false;

	return true;
}

function render_module($view_file_path, $data = array(), $module_name = null)
{
	if ($module_name) $view_file_path = $module_name.'::'.$view_file_path;

	return render($view_file_path, $data);
}

