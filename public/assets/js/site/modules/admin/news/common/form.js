function get_link_form(id) {
	var html = '';
	html += '<div class="row mb10 link_row" id="link_row_' + id + '">';
	html += '<div class="col-sm-6">';
	html += '<label class="sr-only" for="uri">URL</label>';
	html += '<input type="text" class="form-control" name="link_uri[' + id + ']" placeholder="http://example.com ※必須">';
	html += '</div>';
	html += '<div class="col-sm-4">';
	html += '<label class="sr-only" for="link_label">リンク表示</label>';
	html += '<input type="text" class="form-control" name="link_label[' + id + ']" placeholder="リンク表示">';
	html += '</div>';
	html += '<div class="col-sm-2">';
	html += '<button type="button" class="btn btn-danger btn-sm btn_delete_link_row" data-id="' + id + '"><i class="glyphicon glyphicon-trash"></i></button>';
	html += '</div>';
	html += '</div>';

	return html;
}
