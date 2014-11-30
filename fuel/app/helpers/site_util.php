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

function site_get_screen_name($u, $default_value = null)
{
	if (!$u) return is_null($default_value) ? term('guest') : $default_value;

	if (!empty($u->name)) return $u->name;
	if (!empty($u->username)) return $u->username;

	return 'ID:'.$u->id;
}

function conf($item, $default = null, $file = 'site', $replace_delimitter = null)
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
	return term('symbol.'.$key);	
}

function symbol_bool($bool)
{
	return $bool ? symbol('bool.true') : symbol('bool.false');
}

function img($filename = '', $size_key = '', $link_uri = '', $is_link2raw_file = false, $alt = '', $is_profile = false, $is_responsive = false, $anchor_attr = array(), $img_attr = array())
{
	if (!isset($img_attr['class'])) $img_attr['class'] = '';
	if ($is_responsive) $img_attr['class'] = 'img-responsive';

	if (strlen($filename) <= 3)
	{
		$file_cate = $filename;
		$filename = '';
	}
	else
	{
		$file_cate = Site_Upload::get_file_cate_from_filename($filename);
	}

	$additional_table = '';
	if ($is_profile)
	{
		if (conf('upload.types.img.types.m.save_as_album_image'))
		{
			$size_key = 'P_'.$size_key;
			$additional_table = 'profile';
		}
		if (!empty($img_attr['class'])) $img_attr['class'] .= ' ';
		$img_attr['class'] .= 'profile_image';
	}

	if (!$size = img_size($file_cate, $size_key, $additional_table)) $size = $size_key;
	if (empty($filename))
	{
		$noimage_tag = Site_Util::get_noimage_tag($size, $file_cate, $img_attr);
		if ($link_uri) return Html::anchor($link_uri, $noimage_tag, $anchor_attr);

		return $noimage_tag;
	}
	if ($alt) $img_attr['alt'] = $alt;

	$uri_path = Site_Upload::get_uploaded_file_path($filename, $size, 'img', false, true);
	$image_tag = Html::img($uri_path, $img_attr);

	if ($link_uri) return Html::anchor($link_uri, $image_tag, $anchor_attr);

	if ($is_link2raw_file)
	{
		$anchor_attr['target'] = '_blank';
		$uri_path = Site_Upload::get_uploaded_file_path($filename, 'raw', 'img', false, true);
		return Html::anchor($uri_path, $image_tag, $anchor_attr);
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

function get_public_flag_label($public_flag, $view_icon_only = false, $return_type = 'array', $is_hidden_xs = false)
{
	if (!in_array($return_type, array('array', 'icon_term', 'label'))) throw new InvalidArgumentException('Second parameter is invalid.');

	$public_flag_key = 'public_flag.options.'.$public_flag;
	$icon = icon_label($public_flag_key, 'icon', $is_hidden_xs, null, 'fa fa-', 'i');
	$name = $view_icon_only ? '' : icon_label($public_flag_key, 'label', $is_hidden_xs, null, 'fa fa-', 'i');
	if ($return_type == 'icon_term') return $icon.$name;

	$color = Site_Util::get_public_flag_coloer_type($public_flag);
	if ($return_type == 'label') return html_tag('span', array('class' => 'label label-'.$color), $icon.$name);

	return array($name, $icon, 'btn-'.$color);
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

function check_acl($acl_path, $method = 'GET', $is_convert_acl_path = false)
{
	if ($is_convert_acl_path) $acl_path = Site_Util::get_acl_path($acl_path);

	return Auth::has_access($acl_path.'.'.$method);
}

function check_original_user($user_id, $is_admin = false)
{
	return $user_id == conf(sprintf('original_user_id.%s', $is_admin ? 'admin' : 'site'));
}

function isset_datatime($datetime)
{
	if (empty($datetime)) return false;
	if ($datetime == '0000-00-00 00:00:00') return false;

	return true;
}

function check_and_get_datatime($datetime, $type = null, $default_value = '')
{
	if (!isset_datatime($datetime)) return $default_value;

	if (is_null($type)) $type = 'datetime';
	if (!in_array($type, array('date', 'datetime', 'datetime_minutes')))
	{
		throw new InvalidArgumentException('Parameter type is invalid.');
	}

	switch ($type)
	{
		case 'date':
			$length = 10;
			break;
		case 'datetime_minutes':
			$length = 16;
			break;
		case 'datetime':
		default :
			$length = 0;
			break;
	}
	if (!$length) return $datetime;

	return substr($datetime, 0, $length);
}

function is_prod_env()
{
	return Site_Util::check_is_prod_env();
}

function is_dev_env()
{
	return Site_Util::check_is_dev_env();
}

function label_is_secure($value, $view_icon_only = false, $attrs = array())
{
	list($name, $icon_tag, $type) = Site_Util::get_is_secure_label_parts($value);
	$label_name = $view_icon_only ? $icon_tag : $icon_tag.' '.$name;

	if ($view_icon_only)
	{
		$attrs['data-toggle']    = 'tooltip';
		$attrs['data-placement'] = 'top';
		$attrs['title']          = $name;
	}

	return label($label_name, $type, $attrs);
}
