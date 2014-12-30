<?php

function view_convert_list($val)
{
	if (!is_array($val)) return $val;

	$list = array();
	foreach ($val as $row) $list[] = sprintf('<li>%s</li>', $row);

	return sprintf('<ul>%s</ul>', implode(PHP_EOL, $list));
}

function strim($string, $width = 0, $trimmarker = '...', $is_html = true)
{
	if (!$width) return $string;

	$original_width = mb_strlen($string);

	if ($is_html) $string = Site_Util::html_entity_decode($string);
	$string = mb_strimwidth($string, 0, $width, $trimmarker);
	if ($is_html) $string = Security::htmlentities($string);

	return $string;
}

function convert_body($body, $truncate_options = array(), $callbacks = array())
{
	$truncate_options_default = array(
		'is_detail'     => false,
		'encoding'      => Config::get('encoding'),
		'width'         => conf('view_params_default.list.trim_width.body'),
		'line'          => conf('view_params_default.list.truncate_lines.body'),
		'is_rtrim'      => true,
		'trimmarker'    => conf('view_params_default.list.trimmarker'),
		'read_more_uri' => '',
	);
	if (!is_array($truncate_options)) $truncate_options = (array)$truncate_options;
	$truncate_options = $truncate_options + $truncate_options_default;

	return render('_parts/converted_body', array(
		'body' => $body,
		'callbacks' => $callbacks,
		'options' => $truncate_options,
	));
}

function url2link($string)
{
	if (!conf('view_params_default.post.url2link.isEnabled')) return $string;

	$url_pattern = '/https?:\/\/(?:[a-zA-Z0-9_\-\/.,:;~?@=+$%#!()]|&amp;)+/';

	return preg_replace_callback($url_pattern, 'url2link_callback', $string);
}

function url2link_callback($matches)
{
	$url = str_replace('&amp;', '&', $matches[0]);
	$items = parse_url($url);
	$length = conf('view_params_default.post.url2link.textLength');
	$truncated_marker = conf('view_params_default.post.url2link.truncatedMarker');
	$etc = '...';

	if (strlen($url) > $length)
	{
		$length -= strlen($etc);
		$urlstr = substr($url, 0, $length).$truncated_marker;
	}
	else
	{
		$urlstr = $url;
	}
	$target = Site_Util::check_ext_uri($url) ? ' target="_blank"' : '';

	$url    = Security::htmlentities($url);
	$urlstr = Security::htmlentities($urlstr);

	return sprintf('<a href="%s"%s>%s</a>', $url, $target, $urlstr);
}

function mention2link($string)
{
	if (!conf('mention.isEnabled', 'notice')) return $string;

	$conf = conf('member.name');
	$accept_str = $conf['accept_strings'];
	$pattern = sprintf('/(?<![%s])(@|＠)([%s]{%d,%d})(?![%s])/u', $accept_str, $accept_str, $conf['length']['min'], $conf['length']['max'], $accept_str);

	return preg_replace_callback($pattern, 'mention2link_callback', $string);
}

function mention2link_callback($matches)
{
	$member_name = $matches[2];
	if (!$member = Model_Member::get_one4name($member_name)) return $matches[0];

	$url    = Uri::create('member/'.$member->id);
	$urlstr = Security::htmlentities($matches[1].$member_name);

	return sprintf('<a href="%s">%s</a>', $url, $urlstr);
}
