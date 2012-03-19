<?php

function birds_get_bird_image_link($url, $img, $size = 'sml')
{
	$img_path = sprintf('%s/public/img/birds/each/%s/%s', PRJ_BASEPATH, $size, $img);
	if (file_exists($img_path))
	{
		$img_url = sprintf('%simg/birds/each/%s/%s', PRJ_BASE_URL, $size, $img);

		return sprintf('<a href="/birds/%s/"><img src="%s" alt="Picture of %s"></a>', $url, $img_url, $url);
	}

	$img_postfix = '';
	if ($size == 'sml') $img_postfix = '_s';

	return sprintf('<a href="/birds/%s/"><img src="%simg/birds/no_img%s.gif" alt="No Image" /></a>', $url, PRJ_BASE_URL, $img_postfix);
}
