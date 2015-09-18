<!DOCTYPE HTML>
<!--
/*
 * jQuery File Upload Plugin Demo
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
-->
<html lang="jp">
<head>
<!-- Force latest IE rendering engine or ChromeFrame if installed -->
<!--[if IE]>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<![endif]-->
<meta charset="utf-8">
<title>jQuery File Upload Demo</title>
<meta name="description" content="File Upload widget with multiple file selection, drag&amp;drop support, progress bars, validation and preview images, audio and video for jQuery. Supports cross-domain, chunked and resumable file uploads and client-side image resizing. Works with any server-side platform (PHP, Python, Ruby on Rails, Java, Node.js, Go etc.) that supports standard HTML form file uploads.">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap styles -->
<link rel="stylesheet" href="/assets/ccss/bootstrap.custom.css?1442404557">
<!-- blueimp Gallery styles -->
<link rel="stylesheet" href="/assets/css/jquery-file-upload/gallery/blueimp-gallery.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="/assets/css/jquery-file-upload/jquery.fileupload.css">
<link rel="stylesheet" href="/assets/css/jquery-file-upload/jquery.fileupload-ui.css">
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="/assets/css/jquery-file-upload/jquery.fileupload-noscript.css"></noscript>
<noscript><link rel="stylesheet" href="/assets/css/jquery-file-upload/jquery.fileupload-ui-noscript.css"></noscript>
<style>
.preview {
	max-width: 100%;
	text-align: center;
}
</style>
</head>
<body>
<div class="container">
	<!-- The file upload form used as target for the file upload widget -->
	<form id="fileupload" action="//jquery-file-upload.appspot.com/" method="POST" enctype="multipart/form-data">

		<!-- The container for the uploaded files -->
		<div class="" id="upload_files_img">
			<div class="files row" id="files_img"></div><!-- #files -->

			<!-- The global progress bar -->
			<div class="progress progress-striped active" id="progress_img" role="progressbar" aria-valuemin="0" aria-valuemax="100">
				<div class="progress-bar progress-bar-success" style="width:0%;"></div>
			</div>
			<!-- The extended global progress state -->
			<div class="progress-extended">&nbsp;</div>

			<!-- The fileinput-button span is used to style the file input field as button -->
			<div class="fileinput">
				<input type="hidden" value="S" name="thumbnail_size" id="thumbnail_size">
				<div class="form-group">
					<label for="files" class="sr-only">Select files...</label>
					<span class="btn btn-success btn-sm fileinput-button">
						<i class="glyphicon glyphicon-plus"></i>
						<span>Select files...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input type="file" accept="image/*" id="file_select_img" multiple="" name="files[]" class="file_select">
					</span>
				</div><!-- .form-group -->
			</div><!-- fileinput -->

		</div><!-- #upload_files -->


	</form>
</div>
<!-- The blueimp Gallery widget -->
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
	<div class="slides"></div>
	<h3 class="title"></h3>
	<a class="prev">‹</a>
	<a class="next">›</a>
	<a class="close">×</a>
	<a class="play-pause"></a>
	<ol class="indicator"></ol>
</div>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<div class="col-sm-4 col-md-3 template-upload fade">
		<div class="thumbnail">
			<span class="preview thumbnail"></span>
			<div class="caption clearfix">
				<h5>{%=file.name%}</h5>
				<strong class="error text-danger"></strong>
				<p class="subinfo pull-right">
					<span class="size">Processing...</span>
					{% if (!i) { %}
						<button type="reset" class="btn btn-xs btn-warning cancel">
								<i class="glyphicon glyphicon-ban-circle"></i>
								<span>Cancel</span>
						</button>
					{% } %}
				</p>
			</div><!-- caption -->
			<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
				<div class="progress-bar progress-bar-success" style="width:0%;"></div>
			</div>

		</div><!-- thumbnail -->
	</div><!-- col-sm-6 col-md-4 -->

{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<div class="col-sm-4 col-md-3 template-download fade" id="{%=file.id%}">
		<div class="thumbnail">
			<span class="preview thumbnail">
				{% if (file.thumbnailUrl) { %}
					<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery>
						<img src="{%=file.thumbnailUrl%}" alt="{%=file.original_name%}">
					</a>
				{% } %}
			</span>
			<div class="caption clearfix">
				<h5>
					{% if (file.url) { %}
						<a href="{%=file.url%}" title="{%=file.original_name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.original_name%}</a>
					{% } else { %}
						<span>{%=file.name%}</span>
					{% } %}
				</h5>
				{% if (file.error) { %}
					<strong class="error text-danger">Error: {%=file.error%}</strong>
				{% } %}

				<p class="subinfo">
					<span class="size">{%=o.formatFileSize(file.size)%}</span>

					{% if (file.deleteUrl) { %}
						</button>
						<button class="btn btn-xs btn-danger delete_file{% if (file.is_tmp) { %}_tmp{% } %}" data-id="{%=file.id%}" data-file_type="img" data-type="image{% if (file.is_tmp) { %}_tmp{% } %}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
							<i class="glyphicon glyphicon-trash"></i>
						</button>
					{% } else { %}
						<button type="reset" class="btn btn-xs btn-warning cancel">
							<i class="glyphicon glyphicon-ban-circle"></i>
						</button>
					{% } %}
				</p>
				<p>
					<textarea name="image{% if (file.is_tmp) { %}_tmp{% } %}_description[{%=file.id%}]" id="image{% if (file.is_tmp) { %}_tmp{% } %}_description_{%=file.id%}" class="form-control" placeholder="写真の説明" rows="2"></textarea>
				</p>
			</div><!-- caption -->
			<input type="hidden" id="form_image{% if (file.is_tmp) { %}_tmp{% } %}[{%=file.id%}]" value="{%=file.name_prefix%}{%=file.name%}" name="image{% if (file.is_tmp) { %}_tmp{% } %}[{%=file.id%}]" class="image{% if (file.is_tmp) { %}_tmp{% } %}">

		</div><!-- thumbnail -->
	</div><!-- col-sm-6 col-md-4 -->

{% } %}
</script>
<script src="/assets/js/jquery-2.1.3.min.js"></script>
<script src="/assets/js/jquery-file-upload/vendor/jquery.ui.widget.js"></script>
<script src="/assets/js/jquery-file-upload/tmpl.min.js"></script>
<script src="/assets/js/jquery-file-upload/load-image.all.min.js"></script>
<script src="/assets/js/jquery-file-upload/canvas-to-blob.min.js"></script>
<script src="/assets/js/bootstrap.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.blueimp-gallery.min.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.iframe-transport.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.fileupload.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.fileupload-process.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.fileupload-image.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.fileupload-audio.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.fileupload-video.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.fileupload-validate.js"></script>
<script src="/assets/js/jquery-file-upload/jquery.fileupload-ui.js"></script>

<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
<script src="/assets/js/jquery-file-upload/cors/jquery.xdr-transport.js"></script>
<![endif]-->

<script>
$(function() {
	var url = "/filetmp/api/upload.json";        // ※ファイルをアップロードする URL
	$('#fileupload').fileupload({
		url: url,
		dataType: 'json',
		autoUpload: true,       // ※ファイルを選択したら、即、アップロード
		acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		maxFileSize: 5000000, // 5 MB
		// Enable image resizing, except for Android and Opera,
		// which actually support image resizing, but fail to
		// send Blob objects via XHR requests:
		disableImageResize: /Android(?!.*Chrome)|Opera/
			.test(window.navigator.userAgent),
		previewMaxWidth: 192,
		previewMaxHeight: 192,
		previewCrop: true
	}).on('fileuploadadd', function (e, data) {
		data.context = $('#files_img');
		$.each(data.files, function (index, file) {
			var node = $('');
			node.data(data)
			node.appendTo(data.context);
		});
	}).on('fileuploadprocessalways', function (e, data) {
console.log(data);
			var index = data.index,
				file = data.files[index],
				node = $(data.context.children()[index]);
			if (file.preview) {
				node.prepend(file.preview);
			}
	});
});
</script>

</body>
</html>
