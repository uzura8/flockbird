/*jslint unparam: true */
/*global window, $ */
$(function () {
	'use strict';
	// Change this to the location of your server-side upload handler:
	var url = get_url('filetmp/api/upload.html');
	$('#fileupload').fileupload({
		url: url,
		dataType: 'text',
		done: function (e, data) {
			$('#files').append(data['result']).fadeIn('fast');
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

		var delete_uri = (file_type == 'file_tmp') ? 'filetmp/api/upload.json' : 'album/image/api/delete.json';
		delete_item(delete_uri, file_id, '#' + file_type);
		return false;
	});
});
$('.display_fileinput-button').click(function() {
	$('.display_fileinput-button').remove();
	$('.fileinput').attr('class', 'fileinput');
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