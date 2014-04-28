$('#btn_create').click(function(){
	if (GL.execute_flg) return false;
	execute_simple_post(this);
	return false;
});

$(document).on('click','.btn_delete', function(){
	execute_simple_delete(this);
	return false;
});

jqui_sort('admin/news/category/api/update/sort_order.json');
$('body').tooltip({
	selector: 'a[data-toggle=tooltip]',
	placement: 'top'
});
