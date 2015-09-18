<?php echo render('filetmp/_parts/tmpl'); ?>
<?php
echo Asset::js('jquery-file-upload/vendor/jquery.ui.widget.js');
echo Asset::js('jquery-file-upload/tmpl.min.js');// The Templates plugin is included to render the upload/download listings
Asset::js(array(
	'jquery-file-upload/load-image.all.min.js',// The Load Image plugin is included for the preview images and image resizing functionality
	'jquery-file-upload/canvas-to-blob.min.js',// The Canvas to Blob plugin is included for image resizing functionality
	'jquery-file-upload/jquery.blueimp-gallery.min.js',// blueimp Gallery script
	'jquery-file-upload/jquery.iframe-transport.js',// The Iframe Transport is required for browsers without support for XHR file uploads
	'jquery-file-upload/jquery.fileupload.js',// The basic File Upload plugin
	'jquery-file-upload/jquery.fileupload-process.js',// The File Upload processing plugin
	'jquery-file-upload/jquery.fileupload-image.js',// The File Upload image preview & resize plugin
	//'jquery-file-upload/jquery.fileupload-audio.js',// The File Upload audio preview plugin
	//'jquery-file-upload/jquery.fileupload-video.js',// The File Upload video preview plugin
	'jquery-file-upload/jquery.fileupload-validate.js',// The File Upload validation plugin
	'jquery-file-upload/jquery.fileupload-ui.js',// The File Upload user interface plugin
	'site/common/file_tmp_upload.js',// The main application script
), null, 'js_upload', false, true);
echo Asset::render('js_upload', false, 'js');
?>

<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
<?php //echo Asset::js('jquery-file-upload/cors/jquery.xdr-transport.js');?>
<![endif]-->

