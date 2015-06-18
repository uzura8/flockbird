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

function convert_body($body, $options = array())
{
	$handler = new Site_PostedBodyHandler($options);

	return $handler->convert($body);
}

function convert_body_by_format($body, $format = 0, $truncate_width = 0)
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

	$handler = new Site_PostedBodyHandler(array(
		'truncate_width' => $truncate_width,
		'is_strip_tags' => true,
		'nl2br' => false,
		'url2link' => false,
		'truncate_line' => 0,
	));

	return $handler->convert($body);
}

