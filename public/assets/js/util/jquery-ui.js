function jqui_sort(uri){
	var child_handler   = (arguments.length > 1) ? arguments[1] : '.jqui-sortable-handle';
	var parent_selecter = (arguments.length > 2) ? arguments[2] : '#jqui-sortable';
	var child_selecter  = (arguments.length > 3) ? arguments[3] : '.jqui-sortable-item';

	$(parent_selecter).sortable({
		items: child_selecter,
		handle: child_handler,
		update: function(event, ui) {
			var id_list = $(parent_selecter).sortable('toArray').join(',');
			var post_data = {};
			post_data['ids'] = id_list;
			post_data = set_token(post_data);
			$.ajax({
				url : get_url(uri),
				type : 'POST',
				dataType : 'text',
				data : post_data,
				timeout: get_config('default_ajax_timeout'),
				success: function(data){
					$.jGrowl('並び順を変更しました。');
				},
				error: function(data){
					$.jGrowl(get_error_message(data['status'], '並び順を変更できませんでした。'));
				}
			});
		}
	});
}
