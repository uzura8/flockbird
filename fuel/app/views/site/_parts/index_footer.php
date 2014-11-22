<?php
if (Config::get('page.site.index.timeline.isEnabled') && is_enabled('timeline'))
{
	echo render('timeline::_parts/load_timelines');
}
if (Config::get('page.site.index.albumImage.isEnabled') && is_enabled('album'))
{
	echo render('album::image/_parts/list_footer', array('is_not_load_more' => true));
}
?>
