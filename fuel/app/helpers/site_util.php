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

	return (!empty($u->name)) ? $u->name : 'メンバーID:'.$u->id;
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

function anchor_button($href, $icon_class = '', $text = '', $class_attr_add = '', $attr = array(), $is_mini_btn = false, $is_sp = false, $is_force_btn = false, $is_force_loud_color = false)
{
	$class_attrs  = array('btn', 'btn-default');
	if ($is_mini_btn) $class_attrs[] = 'btn-xs';

	if ($is_sp && !$is_force_btn)
	{
		$class_attrs = array();
		if (!$is_force_loud_color) $class_attrs = array('cl-modest');
	}
	$class_attr = implode(' ', $class_attrs);
	if ($class_attr_add) $class_attr .= ' '.$class_attr_add;

	if (!empty($attr['class'])) $class_attr .= ' '.$attr['class'];
	$attr['class'] = $class_attr;

	$element = '';
	if ($icon_class) $element = sprintf('<i class="%s"></i>', $icon_class);
	if ($text) $element .= ' '.$text;

	return Html::anchor($href, $element, $attr);
}

function term($key)
{
	return Config::get('term.'.$key);
}

function alert($message, $type = 'info', $with_dismiss_btn = false)
{
 return render('_parts/alerts', array('message' => $message, 'type' => $type, 'with_dismiss_btn' => $with_dismiss_btn));
}

function small_tag($str, $is_enclose_small_tag = true)
{
	return sprintf('%s%s%s', $is_enclose_small_tag ? '<small>' : '', $str, $is_enclose_small_tag ? '</small>' : '');
}

function btn($type, $href = '#', $class_name = '', $with_text = false, $size = '', $btn_type = 'default', $attr = array(), $exception_label = '')
{
	switch ($type)
	{
		case 'edit':
			$label_text = '編集';
			$label_icon  = 'glyphicon glyphicon-edit';
			break;
		case 'delete':
			$label_text = '削除';
			$label_icon  = 'glyphicon glyphicon-trash';
			break;
		default :
			throw new \InvalidArgumentException("First parameter must be 'edit' or 'delete'.");
			break;
	}

	$label  = sprintf('<i class="%s"></i>', $label_icon);
	$label .= $with_text ? ' '.$label_text : '';

	$class_items   = array();
	$class_items[] = 'btn';
	$class_items[] = 'btn-'.$btn_type;
	if ($class_name) $class_items[] = $class_name;
	if ($size) $class_items[] = 'btn-'.$size;
	if (isset($attr['class'])) $class_items[] = $attr['class'];
	$attr['class'] = implode(' ', $class_items);

	return Html::anchor($href, $label, $attr);
}

function check_profile_public_flag($public_flag, $access_from)
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

function profile_value(Model_MemberProfile $member_profile)
{
	if (in_array($member_profile->profile->form_type, array('checkbox', 'select', 'radio')))
	{
		return $member_profile->profile_option->label;
	}

	return $member_profile->value;
}

?>
