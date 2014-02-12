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
		<?php echo Form::textarea($name, Input::post($name, $default_value), $atter); ?>
<?php if ($val->error($name)): ?>
		<span class="help-block error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
<?php if ($optional_public_flag): ?>
		<div class="row">
			<div class="col-sm-4 col-sm-offset-8">
				<?php echo field_public_flag($optional_public_flag['value'], 'select', array(), $optional_public_flag['name']); ?>
			</div>
		</div>
<?php endif; ?>
<?php if (!empty($help)): ?>
		<span class="help-block"><?php echo $help; ?></span>
<?php endif; ?>
	</div>
</div>
