<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
	$input_atter['required'] = 'required';
}
?>
<div class="control-group">
	<?php echo Form::label($label, $name, array('class' => 'control-label')); ?>
	<div class="controls">
		<?php echo Form::textarea($name, Input::post($name, $default_value), $atter); ?>
<?php if ($val->error($name)): ?>
		<span class="help-inline error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
	</div>
</div>
