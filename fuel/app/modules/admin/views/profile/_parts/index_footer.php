<?php echo render('_parts/sortable_footer'); ?>
<script>
$(function(){
	jqui_sort('admin/profile/api/update/sort_order.json');
	$('body').tooltip({
		selector: 'a[data-toggle=tooltip]',
		placement: 'top'
	});
});
</script>
