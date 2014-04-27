$('#btn_create').click(function(){
	if (GL.execute_flg) return false;

	var label = $('#input_label').val().trim();
	var post_uri = 'admin/news/category/api/create.html';
	var post_data = {};
	post_data['name'] = label;
	var msg_success = '作成しました。';
	var msg_error = '作成に失敗しました。';
	send_article(this, post_data, post_uri, '#jqui-sortable', false, '#input_label', msg_success, msg_error);

	return false;
});

$(document).on('click','.btn_delete', function(){
	var post_id  = parseInt($(this).data('id'));
	var post_uri  = $(this).data('uri');
	if (!post_id || !post_uri) return false;

	delete_item(post_uri, post_id, '', '#' + post_id);

	return false;
});

jqui_sort('admin/news/category/api/update/sort_order.json');
$('body').tooltip({
	selector: 'a[data-toggle=tooltip]',
	placement: 'top'
});
