<?php
$feed_options = array(
	'link'    => Uri::create($link_uri),
	'caption' => strim(!empty($caption) ? $caption : FBD_SITE_DESCRIPTION, conf('service.facebook.shareDialog.caption.trimWidth'), null, false, true),
	'name'    => strim($name, conf('service.facebook.shareDialog.name.trimWidth'), null, false, true),
);
if (!empty($description)) $feed_options['description'] = strim($description, conf('service.facebook.shareDialog.description.trimWidth'), null, false, true);
if (!empty($images))
{
	$image = Util_Array::get_last($images);
	if (empty($img_size_key)) $img_size_key = 'thumbnail';
	$feed_options['picture'] = Uri::create(img_uri($image->get_image(), $img_size_key));
}

echo btn('service.facebook.do_share', '#', 'js-facebook_feed', true, 'xs', 'primary',
			array('class' => 'ml10', 'data-options' => json_encode($feed_options)), null, null, null, false);
?>
<div id="fb-root"></div>

