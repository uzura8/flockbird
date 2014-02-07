<?php echo Asset::js('jquery-ui-1.10.3.custom.min.js');?>
<?php echo Asset::js('util/jquery-ui.js');?>
<script>
$(function(){
	jqui_sort('admin/profile/api/update_sort_order.json');
	$('body').tooltip({
		selector: 'a[data-toggle=tooltip]',
		placement: 'top'
	});
});
</script>
