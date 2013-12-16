<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
	$input_atter['required'] = 'required';
}
?>
<div class="form-group">
<?php if (strlen($label)): ?>
	<?php echo Form::label($label, $name, array('class' => 'control-label col-sm-2')); ?>
<?php endif; ?>
	<div class="col-sm-10">
		<?php echo Form::input($name, null, $input_atter); ?>
<?php if (!empty($val) && $val->error($name)): ?>
		<span class="help-inline error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
	</div>
</div>
