$(document).on('click','.add_link', function(){
	var current_id = parseInt($('#link_id_max').val());

	var form_html = get_link_form(current_id + 1);
	$('#link_list').append(form_html);
	$('#link_id_max').val(current_id + 1)
	return false;
});

$(document).on('click','.btn_delete_link_row', function(){
	var target_id = $(this).data('id') ? parseInt($(this).data('id')) : 0;
	var is_saved = $(this).data('is_saved') ? parseInt($(this).data('is_saved')) : 0;
	apprise('削除します。よろしいですか?', {'confirm':true}, function(r) {
		if (r == true) {
			var row_id = '#link_row_';
			if (is_saved) row_id += 'saved_';
			row_id += target_id;

			$(row_id).remove();
		}
	});
	return false;
});

function changeAttrForEitor(isSummernote) {
	var bodySelector = isSummernote ? '.note-editable' : '#form_body';
	var insertAsElementValue = isSummernote ? 1 : 0;
	$('#insert_target').val(bodySelector);
	$('.js-insert_img').each(function() {
		var btn = document.getElementById($(this).attr('id'));
		btn.setAttribute('data-body', bodySelector);
	});
}

function checkInput() {
	if ($('#form_title').val().length > 0) return true;
	if ($('.note-editable').size() > 0 && $('.note-editable').html().replace(/^<br>\s*/, '').size() > 0) return true;
	if ($('#form_body').val().length > 0) return true;
	if ($('.image_tmp').size() > 0 && $('.image_tmp').length) return true;
	if ($('.file_tmp').size() > 0 && $('.file_tmp').length) return true;
	if ($('.link_uri').size() > 0 && $('.link_uri').length) return true;
	if ($('.link_label').size() > 0 && $('.link_label').length) return true;
	if ($('#form_published_at_time').val()) return true;
	return false;
}

function get_link_form(id) {
	var html = '';
	html += '<div class="row mb10 link_row" id="link_row_' + id + '">';
	html += '<div class="col-sm-6">';
	html += '<label class="sr-only" for="uri">URL</label>';
	html += '<input type="text" class="form-control link_uri" name="link_uri[' + id + ']" placeholder="http://example.com ※必須">';
	html += '</div>';
	html += '<div class="col-sm-4">';
	html += '<label class="sr-only" for="link_label">リンク表示</label>';
	html += '<input type="text" class="form-control link_label" name="link_label[' + id + ']" placeholder="リンク表示">';
	html += '</div>';
	html += '<div class="col-sm-2">';
	html += '<button type="button" class="btn btn-danger btn-sm btn_delete_link_row" data-id="' + id + '"><i class="glyphicon glyphicon-trash"></i></button>';
	html += '</div>';
	html += '</div>';

	return html;
}
