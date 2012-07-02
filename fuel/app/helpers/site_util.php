<?php

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

function site_profile_image($member_image, $size, $uri = '', $is_link_to_lerge_image = false)
{
	$config = Config::get('site.image.member');
	if (empty($config[$size])) $size = 'medium';

	$config['alt'] = 'No image.';
	$config['width'] = $config[$size]['width'];
	$config['class'] = 'profile_image';
	$noimage_tag = Html::img('upload/img/member/noimage.gif', $config);
	$file = sprintf('%s/img/member/%s/%s', PRJ_UPLOAD_DIR, $size, $member_image);

	if (empty($member_image) || !file_exists($file))
	{
		if (!empty($uri)) return Html::anchor($uri, $noimage_tag);

		return $noimage_tag;
	}

	$image_uri = sprintf('upload/img/member/%s/%s', $size, $member_image);
	$image_tag = Html::img($image_uri, array('class' => 'profile_image'));

	if (!empty($uri)) return Html::anchor($uri, $image_tag);
	if ($is_link_to_lerge_image) return Html::anchor('upload/img/member/lerge/'.$member_image, $image_tag);

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
