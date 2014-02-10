$('#btn_create').click(function(){
	if (GL.execute_flg) return false;

	var label = $('#input_label').val().trim();
	var id = parseInt($(this).data('id'));
	if (label.length == 0 || id == 0) return false;

	var post_uri = 'admin/profile/option/api/create.html';
	var post_data = {};
	post_data['id'] = id;
	post_data['label'] = label;
	var msg_success = '選択しを作成しました。';
	var msg_error = '選択しを作成に失敗しました。';
	send_article(this, post_data, post_uri, '#jqui-sortable', false, '#input_label', msg_success, msg_error);

	return false;
});

$(document).on('click','.btn_profile_delete', function(){
	var post_id  = parseInt($(this).data('id'));
	if (!post_id) return false;

	var post_uri = 'admin/profile/option/api/delete.json';
	delete_item(post_uri, post_id, '', '#' + post_id);

	return false;
});

jqui_sort('admin/profile/option/api/update_sort_order.json');
$('body').tooltip({
	selector: 'a[data-toggle=tooltip]',
	placement: 'top'
});
