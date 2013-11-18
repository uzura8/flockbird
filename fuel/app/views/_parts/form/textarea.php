<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
	$atter['required'] = 'required';
}
?>
<div class="form-group">
	<?php echo Form::label($label, $name, array('class' => 'control-label col-sm-2')); ?>
	<div class="col-sm-10">
		<?php echo Form::textarea($name, Input::post($name, $default_value), $atter); ?>
<?php if ($val->error($name)): ?>
		<span class="help-inline error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
	</div>
</div>
