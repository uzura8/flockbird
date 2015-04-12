<?php
Asset::js(array(
	'jquery-ui-1.10.3.custom.js',
	'util/jquery-ui.js',
), null, 'js_jquery_ui', false, true);
echo Asset::render('js_jquery_ui', false, 'js');
?>

