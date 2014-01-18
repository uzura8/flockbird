<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
	$input_atter['required'] = 'required';
}

$label_class = 'col-sm-'.$label_col_sm_size;
$input_col_sm_size = 12 - $label_col_sm_size;
if ($label_col_sm_size == 12)
{
	$input_col_sm_size = 12;
}
else
{
	$label_class .= ' control-label';
}
?>
<div class="form-group<?php if ($val->error($name)): ?> has-error<?php endif; ?>">
	<?php echo Form::label($label, $name, array('class' => $label_class)); ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?>">
		<div class="row">
			<div class="col-sm-<?php echo $col_sm_size; ?>">
				<?php echo Form::input($name, Input::post($name, $default_value), $input_atter); ?>
			</div>
<?php if ($val->error($name)): ?>
			<div class="col-sm-12">
				<span class="help-block error_msg"><?php echo $val->error($name)->get_message(); ?></span>
			</div>
<?php endif; ?>
		</div>
	</div>
</div>
