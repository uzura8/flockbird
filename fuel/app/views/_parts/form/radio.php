<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
	$atter['required'] = 'required';
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
<div class="form-group<?php if ($val->error($name)): ?> has-error<?php endif; ?>" id="<?php echo $atter['id']; ?>_block">
	<?php echo Form::label($label, $name, array('class' => $label_class)); ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?>">
<?php foreach ($options as $value => $label): ?>
<?php if (!$is_inline_options): ?>
		<div class="radio">
<?php endif; ?>
			<label<?php if ($is_inline_options): ?> class="radio-inline"<?php endif; ?>>
<?php
$atter['id'] = sprintf('form_%s_%s', $name, $value);
echo Form::radio($name,$value, Input::post($name, $default_value) == $value, $atter);
?>
				<?php echo $label; ?>
			</label>
<?php if (!$is_inline_options): ?>
		</div>
<?php endif; ?>
<?php endforeach; ?>
<?php if ($val->error($name)): ?>
		<span class="help-block error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
<?php if (!empty($help)): ?>
		<span class="help-block"><?php echo $help; ?></span>
<?php endif; ?>
	</div>
</div>
