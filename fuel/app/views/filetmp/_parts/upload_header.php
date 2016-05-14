<!-- jquery-file-upload styles -->
<?php
Asset::css(array(
	'jquery-file-upload/jquery.fileupload.css',
	'jquery-file-upload/jquery.fileupload-ui.css',
	'fb-jquery-file-upload.css',
	'blueimp-gallery/blueimp-gallery.css',
), null, 'css_jquery_file_upload', false, true);
echo Asset::render('css_jquery_file_upload', false, 'css');
?>

