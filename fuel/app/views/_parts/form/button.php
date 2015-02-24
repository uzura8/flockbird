<?php
$input_col_sm_size = 12 - $offset_size;
if ($label)
{
	$label_class = 'control-label col-sm-'.$offset_size;
	$input_col_class = 'col-sm-'.$input_col_sm_size;
}
else
{
	$input_col_class = sprintf('col-sm-offset-%d col-sm-%d', $offset_size, $input_col_sm_size);
}
?>
<div class="form-group">
<?php if ($label): ?>
	<?php echo Form::label($label, null, array('class' => $label_class)); ?>
<?php endif; ?>
	<div class="<?php echo $input_col_class; ?>">
	<?php echo Form::button($name, $button_label, $atter); ?>
	</div>
</div>
