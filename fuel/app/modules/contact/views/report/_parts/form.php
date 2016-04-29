<?php
$col_sm_size = 12;
$label_col_sm_size = 3;
?>

<div class="modal-header">
	<h3 class="modal-title"><?php echo term('form.post_report'); ?></h3>
</div>
<a href="#" class="btn_modal_close_top btn-xs" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></a>

<div class="modal-body">
<?php echo form_open(); ?>

<?php
if ($confs = conf('report.fields.pre', 'contact'))
{
	foreach ($confs as $name => $props)
	{
		$method = 'form_'.$props['attr']['type'];
		echo $method($val, $name, null, $col_sm_size, $label_col_sm_size);
	}
}
?>

	<?php echo form_textarea($val, 'body', null, $label_col_sm_size); ?>

<?php
if ($confs = conf('report.fields.post', 'contact'))
{
	foreach ($confs as $name => $props)
	{
		$method = 'form_'.$props['attr']['type'];
		echo $method($val, $name, null, $col_sm_size, $label_col_sm_size);
	}
}
?>

	<?php echo form_button('form.post_report', 'button', '', array(
		'class' => 'js-post_report btn btn-primary',
		'data-uri' => 'contact/report/api/send.json',
		'data-post_data' => json_encode($report_data),
	), $label_col_sm_size); ?>

<?php echo form_close(); ?>
</div>

<div class="modal-footer">
	<div class="clearfix">
		<button type="button" class="btn btn-default pull-right" data-dismiss="modal">Close</button>
	</div>
</div>

