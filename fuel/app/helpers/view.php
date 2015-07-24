<?php

function view_convert_list($val)
{
	if (!is_array($val)) return $val;

	$list = array();
	foreach ($val as $row) $list[] = sprintf('<li>%s</li>', $row);

	return sprintf('<ul>%s</ul>', implode(PHP_EOL, $list));
}

function strim($string, $width = 0, $trimmarker = null, $is_html = true, $is_remove_line_feed = false)
{
	if (!$width) return $string;
	if (is_null($trimmarker)) $trimmarker = '...';

	if ($is_remove_line_feed) $string = str_replace(array("\r", "\n"), '', $string);
	$original_width = mb_strlen($string);

	if ($is_html) $string = Site_Util::html_entity_decode($string);
	$string = mb_strimwidth($string, 0, $width, $trimmarker);
	if ($is_html) $string = Security::htmlentities($string);

	return $string;
}

function convert_body($body, $options = array())
{
	$handler = new Site_PostedBodyHandler($options);

	return $handler->convert($body);
}

function convert_body_by_format($body, $format = 0, $truncate_width = 0, $read_more_uri = '')
{
	switch ($format)
	{
		case 1:// raw(html_editor)
			break;
		case 2:
			$body = Markdown::parse($body);// markdown
			break;
		default:
			$body = '';
			break;
	}
	if (!$truncate_width) return $body;

	$options = array(
		'truncate_width' => $truncate_width,
		'is_strip_tags' => true,
		'nl2br' => false,
		'url2link' => false,
		'truncate_line' => 0,
	);
	if ($read_more_uri) $options['read_more_uri'] = $read_more_uri;
	$handler = new Site_PostedBodyHandler($options);

	return $handler->convert($body);
}

