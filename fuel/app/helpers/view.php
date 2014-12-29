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
