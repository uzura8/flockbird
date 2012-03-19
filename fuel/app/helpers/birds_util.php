<?php

function birds_get_bird_image_link($url, $img, $size = '')
{
	return sprintf('<a href="/birds/%s/">%s</a>', $url, birds_get_bird_image_tag($url, $img, $size));
}

function birds_get_bird_image_tag($url, $img, $size = '', $width = 0)
{
	$img_path = sprintf('%s/public/img/birds/each/%s', PRJ_BASEPATH, $img);
	if ($size) $img_path = sprintf('%s/public/img/birds/each/%s/%s', PRJ_BASEPATH, $size, $img);
	if (file_exists($img_path))
	{
		$img_url = sprintf('%simg/birds/each/%s', PRJ_BASE_URL, $img);
		if ($size) $img_url = sprintf('%simg/birds/each/%s/%s', PRJ_BASE_URL, $size, $img);

		$width_property = '';
		if ($width) $width_property = sprintf(' width="%d"', $width);

		return sprintf('<img src="%s" alt="Picture of %s"%s>', $img_url, $url, $width_property);
	}

	$img_postfix = '_s';
	//if ($size == 'sml') $img_postfix = '_s';

	return sprintf('<img src="%simg/birds/no_img%s.gif" alt="No Image" />', PRJ_BASE_URL, $img_postfix);
}
