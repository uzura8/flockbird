<?php
$label_class = 'col-sm-'.$label_col_sm_size;
$text_col_sm_size = 12 - $label_col_sm_size;
if ($label_col_sm_size == 12)
{
	$text_col_sm_size = 12;
}
else
{
	$label_class .= ' control-label';
}
?>
<div class="form-group">
	<?php echo Form::label($label, '', array('class' => $label_class)); ?>
	<div class="form-text col-sm-<?php echo $text_col_sm_size; ?>"><?php echo $value; ?></div>
</div>
