/*jslint unparam: true */
/*global window, $ */
$(function () {
	'use strict';
	var uri = check_is_admin() ? 'admin/filetmp/api/upload.html' : 'filetmp/api/upload.html';
	var post_url = get_url(uri);
	// Change this to the location of your server-side upload handler:
	$('#fileupload').fileupload({
		url: post_url,
		dataType: 'text',
		formData: {thumbnail_size: $('#thumbnail_size').val()},
		start: function (e) {
			$('#progress .progress-bar').css('width', 0);
		},
		done: function (e, data) {
			$('#files').append(data['result']).fadein('fast');
		},
		progressall: function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#progress .progress-bar').css(
				'width',
				progress + '%'
			);
		}
	}).prop('disabled', !$.support.fileInput)
		.parent().addClass($.support.fileInput ? undefined : 'disabled');

	$(document).on('click','.delete_file_tmp', function(){
		var file_id   = $(this).data('id') ? parseInt($(this).data('id')) : 0;
		var file_type = $(this).data('type') ? $(this).data('type') : 'file_tmp';
		var model = $(this).data('model') ? $(this).data('model') : 'album';

		if (model.length > 0 && model != 'album' && model != 'news') model = 'album';
		var delete_uri = '';
		if (file_type == 'file_tmp') {
			delete_uri = 'filetmp/api/upload.json';
		} else {
			delete_uri = model + '/image/api/delete.json';
		}
		if (check_is_admin()) delete_uri = 'admin/' + delete_uri;

		delete_item(delete_uri, file_id, '#' + file_type);
		return false;
	});
});
$('.display_fileinput-button').click(function() {
	$('.display_fileinput-button').remove();
	$('#upload_files').removeClass('hidden');
});

function load_file_tmp(get_url, file_name, parent_attr) {
	var parentDomElement = $(parent_attr);

	var get_data = {};
	get_data['nochache']  = (new Date()).getTime();
	get_data['file'] = file_name;

	$.ajax({
		url : get_url,
		type : 'GET',
		dataType : 'text',
		data : get_data,
		timeout: 10000,
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
