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

function truncate_lines($body, $line, $read_more_uri = '', $is_convert_nl2br = true, $trimmarker = '...')
{
	list($body, $is_truncated) = Util_String::truncate_lines($body, $line, $trimmarker, true, Config::get('encoding'));

	return render('_parts/truncated_body', array(
		'body' => $body,
		'is_truncated' => $is_truncated,
		'read_more_uri' => $read_more_uri,
		'is_convert_nl2br' => $is_convert_nl2br,
	));
}

function convert_body($body, $truncate_lines_props = array(), $callbacks = array())
{
	$is_truncated = false;
	$read_more_uri = '';
	if ($truncate_lines_props)
	{
		$line = !empty($truncate_lines_props['line']) ? $truncate_lines_props['line'] : conf('view_params_default.list.truncate_lines_props.body');
		$trimmarker = !empty($truncate_lines_props['trimmarker']) ? $truncate_lines_props['trimmarker'] : conf('view_params_default.list.truncate_lines.trimmarker');
		list($body, $is_truncated) = Util_string::truncate_lines($body, $line, $trimmarker, true, Config::get('encoding'));
		if (!empty($truncate_lines_props['read_more_uri'])) $read_more_uri = $truncate_lines_props['read_more_uri'];
	}

	return render('_parts/converted_body', array(
		'body' => $body,
		'callbacks' => $callbacks,
		'is_truncated' => $is_truncated,
		'read_more_uri' => $read_more_uri,
	));
}

function url2link($string)
{
	$url_pattern = '/https?:\/\/(?:[a-zA-Z0-9_\-\/.,:;~?@=+$%#!()]|&amp;)+/';

	return preg_replace_callback($url_pattern, 'url2link_callback', $string);
}

function url2link_callback($matches)
{
	$url = str_replace('&amp;', '&', $matches[0]);
	$items = parse_url($url);
	$length = conf('view_params_default.post.link.text_length');
	$etc = '...';

	if (strlen($url) > $length)
	{
		$length -= strlen($etc);
		$urlstr = substr($url, 0, $length) . $etc;
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
