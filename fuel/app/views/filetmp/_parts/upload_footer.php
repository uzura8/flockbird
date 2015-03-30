<?php
Asset::js(array(
	'jquery.ui.widget.js',
	'jquery.iframe-transport.js',
	'jquery.fileupload.js',
	'site/common/file_tmp_upload.js',
), null, 'js_upload', false, true);
echo Asset::render('js_upload', false, 'js');
?>

