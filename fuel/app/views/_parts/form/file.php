<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
	$input_attr_additional['required'] = 'required';
}
?>
<div class="form-group">
<?php if (strlen($label)): ?>
	<?php echo Form::label($label, $name, array('class' => 'control-label col-sm-2')); ?>
<?php endif; ?>
	<div class="<?php if (isset($label)): ?>col-sm-10 col-sm-offset-2<?php else: ?>col-sm-12<?php endif; ?>">
		<?php echo render('_parts/field/input_file', array(
			'name' => $name,
			'input_attr_additional' => $input_attr_additional,
			'accept_type' => $accept_type,
		)); ?>
<?php if (!empty($val) && $val->error($name)): ?>
		<span class="help-inline error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
	</div>
</div>
