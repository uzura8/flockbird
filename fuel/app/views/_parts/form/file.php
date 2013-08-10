<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
	$input_atter['required'] = 'required';
}
?>
<div class="control-group">
<?php if (strlen($label)): ?>
	<?php echo Form::label($label, $name, array('class' => 'control-label')); ?>
<?php endif; ?>
	<div class="controls">
		<?php echo Form::input($name, null, array('type' => 'file', 'class' => 'input-file')); ?>
<?php if (!empty($val) && $val->error($name)): ?>
		<span class="help-inline error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
	</div>
</div>
