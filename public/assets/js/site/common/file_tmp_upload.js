/*jslint unparam: true */
/*global window, $ */
$(function () {
	//'use strict';
	// Change this to the location of your server-side upload handler:
	var startCountFile = 0;
	var endCountFile = 0;
	$('#file_select_file').fileupload({
		url: get_file_upload_post_uri('file'),
		dataType: 'text',
		autoUpload: true,
		singleFileUploads: false,
		recalculateProgress: true,
		formData: {},
		start: function (e) {
			$('#btn_timeline').attr('disabled', 'disabled');
			$('.submit_btn').attr('disabled', 'disabled');
			$('#progress_file').removeClass('hidden');
			$('#progress_file .progress-bar').css('width', 0);
			displayLoading();
		},
		stop: function (e, data) {
			startCountFile = 0;
			endCountFile = 0;
		},
		send: function (e, data) {
			startCountFile++;
		},
		done: function (e, data) {
			endCountFile++;
			var addElement = $(data['result']);
			$('#files_file').append(addElement).fadeIn('fast');
			addElement.ready(function() {
				$('#btn_timeline').removeAttr('disabled');
				$('.submit_btn').removeAttr('disabled');
				displayLoading(true);
			});
		},
		progressall: function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#progress_file .progress-bar').css(
				'width',
				progress + '%'
			);
		}
	}).prop('disabled', !$.support.fileInput)
		.parent().addClass($.support.fileInput ? undefined : 'disabled');

	$('#file_select_img').fileupload({
		url: $('#post_uri').val() ? get_url($('#post_uri').val()) : get_file_upload_post_uri('img'),
		dataType: 'json',
		autoUpload: true,
		filesContainer: '#files_img',
		acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		maxFileSize: get_config('upload_max_filesize'),
		imageMaxWidth: get_config('imageMaxWidth'),
		imageMaxHeight: get_config('imageMaxHeight'),
		// Enable image resizing, except for Android and Opera,
		// which actually support image resizing, but fail to
		// send Blob objects via XHR requests:
		disableImageResize: /Android(?!.*Chrome)|Opera/
			.test(window.navigator.userAgent),
		previewMaxWidth: 192,
		previewMaxHeight: 192,
		previewCrop: true,
		start: function (e) {
			$('#btn_timeline').attr('disabled', 'disabled');
			$('.submit_btn').attr('disabled', 'disabled');
		},
		stop: function (e) {
			$('#btn_timeline').removeAttr('disabled');
			$('.submit_btn').removeAttr('disabled');
		}
	}).on('fileuploadadd', function (e, data) {
		$('#btn_timeline').attr('disabled', 'disabled');
		$('.submit_btn').attr('disabled', 'disabled');
		$.each(data.files, function (index, file) {
			var node = $();
			node.data(data)
			node.appendTo(data.context);
		});
	}).on('fileuploadprocessalways', function (e, data) {
			var index = data.index,
				file = data.files[index],
				node = $(data.context.children()[index]);
			if (file.preview) {
				node.prepend(file.preview);
			}
	});

	$(document).on('click','.delete_file_tmp', function(){
		var file_id   = $(this).data('id') ? parseInt($(this).data('id')) : 0;
		var upload_type = $(this).data('type') ? $(this).data('type') : 'file_tmp';
		var file_type = $(this).data('file_type') ? $(this).data('file_type') : 'img';
		var model = $(this).data('model') ? $(this).data('model') : 'album';
		var parentSelector = '#' + upload_type + '_' + file_id;

		if (model.length > 0 && model != 'album' && model != 'news') model = 'album';

		var delete_uri = '';
		if (check_is_admin()) delete_uri += 'admin/';
		if (upload_type == 'file_tmp' || upload_type == 'image_tmp') {
			delete_uri += 'filetmp/api/upload';
			if (file_type == 'file') delete_uri += '/file';
		} else {
			delete_uri += model;
			delete_uri += (file_type == 'file') ? '/file' : '/image';
			delete_uri += '/api/delete';
		}
		delete_uri += '.json';

		delete_item(delete_uri, parentSelector, file_id);
		return false;
	});
});

function load_file_tmp(get_url, file_name, parent_attr) {
	var parentDomElement = $(parent_attr);

	var get_data = {};
	get_data['file'] = file_name;

	$.ajax({
		url : get_url,
		type : 'GET',
		dataType : 'text',
		data : get_data,
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			$(parentDomElement).html(get_loading_image_tag(true));
		},
		complete: function(xhr, textStatus) {
		},
		success: function(result) {
			$(parentDomElement).children('.loading_image').remove();
			$(parentDomElement).append(result).fadeIn('fast');
		},
		error: function(result) {
			$.jGrowl(get_error_message(result['status'], '読み込みに失敗しました。'));
		}
	});
}

function get_file_upload_post_uri(type) {
	var uri = (type == 'file') ? 'filetmp/api/upload/file.html' : 'filetmp/api/upload.json';
	if (check_is_admin()) uri = 'admin/' + uri;
	return get_url(uri);
}
