$(function(){
	$(document).on('click','.add_link', function(){
		var current_id = parseInt($('#link_id_max').val());

		var form_html = get_link_form(current_id + 1);
		$('#link_list').append(form_html);
		$('#link_id_max').val(current_id + 1)
		return false;
	});

	$(document).on('click','.btn_delete_link_row', function(){
		var target_id = $(this).data('id') ? parseInt($(this).data('id')) : 0;
		apprise('削除します。よろしいですか?', {'confirm':true}, function(r) {
			if (r == true) {
				$('#link_row_' + target_id).remove();
			}
		});
		return false;
	});
});
