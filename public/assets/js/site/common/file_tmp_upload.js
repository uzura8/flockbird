/*jslint unparam: true */
/*global window, $ */
$(function () {
	'use strict';
	// Change this to the location of your server-side upload handler:
	$('#file_select_file').fileupload({
		url: get_file_upload_post_uri('file'),
		dataType: 'text',
		formData: {},
		start: function (e) {
			$('#progress_file .progress-bar').css('width', 0);
		},
		done: function (e, data) {
			$('#files_file').append(data['result']).fadeIn('fast');
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
		dataType: 'text',
		formData: {
			thumbnail_size: $('#thumbnail_size').val(),
			insert_target: $('#insert_target').val()
		},
		start: function (e) {
			$('#progress_img .progress-bar').css('width', 0);
		},
		done: function (e, data) {
			$('#btn_timeline').removeAttr('disabled');
			$('#form_button').removeAttr('disabled');
			$('#files_img').append(data['result']).fadeIn('fast');
		},
		progressall: function (e, data) {
			$('#btn_timeline').attr('disabled', 'disabled');
			$('#form_button').attr('disabled', 'disabled');
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#progress_img .progress-bar').css(
				'width',
				progress + '%'
			);
		}
	}).prop('disabled', !$.support.fileInput)
		.parent().addClass($.support.fileInput ? undefined : 'disabled');

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
$('.btn_disp_file_select').click(function() {
	$('#btn_disp_file_select_' + $(this).data('type')).remove();
	$('#upload_files_' + $(this).data('type')).removeClass('hidden');
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
	var uri = (type == 'file') ? 'filetmp/api/upload/file.html' : 'filetmp/api/upload.html';
	if (check_is_admin()) uri = 'admin/' + uri;
	return get_url(uri);
}
