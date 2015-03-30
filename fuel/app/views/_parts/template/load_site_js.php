<?php
Asset::js(array(
	'site/util.js',
	'site.js',
	'site/handlebars_helpers.js',
), null, 'js_site', false, true);
echo Asset::render('js_site', false, 'js');
?>

