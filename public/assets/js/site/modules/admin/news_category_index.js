$('#btn_create').click(function(){
	if (GL.execute_flg) return false;
	var post_keys = ['name', 'label'];
	execute_simple_post(this, post_keys);
	return false;
});

jqui_sort('admin/news/category/api/update/sort_order.json');
$('body').tooltip({
	selector: 'a[data-toggle=tooltip]',
	placement: 'top'
});
