$('#btn_create').click(function(){
	if (GL.execute_flg) return false;
	execute_simple_post(this);
	return false;
});

jqui_sort('admin/profile/option/api/update_sort_order.json');
$('body').tooltip({
	selector: 'a[data-toggle=tooltip]',
	placement: 'top'
});
