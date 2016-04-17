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

$error_sm_size = 12 - $col_sm_size;
if (!$col_sm_size || $col_sm_size == 12 || $optional_public_flag)
{
	$error_sm_size = 12;
}
?>
<div class="form-group<?php if ($val->error($name)): ?> has-error<?php endif; ?>" id="<?php echo $input_atter['id']; ?>_block">
	<?php echo Form::label($label, $name, array('class' => $label_class)); ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?>">
		<div class="row">
			<div class="col-sm-<?php echo $col_sm_size; ?>">
				<?php echo Form::input($name, (isset($input_value) && !is_null($input_value)) ? $input_value : Input::post($name, $default_value), $input_atter); ?>
			</div>
<?php if ($optional_public_flag): ?>
			<div class="col-xs-12 col-sm-4 col-sm-offset-<?php echo (8 - $col_sm_size); ?> pull-right">
				<?php echo field_public_flag($optional_public_flag['value'], 'select', array('class' => 'input-medium'), $optional_public_flag['name']); ?>
			</div>
<?php endif; ?>
<?php if ($val->error($name) && $error_sm_size < 12): ?>
			<div class="col-sm-<?php echo $error_sm_size; ?>">
				<span class="help-block error_msg"><?php echo $val->error($name)->get_message(); ?></span>
			</div>
<?php endif; ?>
		</div>
<?php if ($val->error($name) && $error_sm_size == 12): ?>
		<div class="row">
			<div class="col-sm-12">
				<span class="help-block error_msg"><?php echo $val->error($name)->get_message(); ?></span>
			</div>
		</div>
<?php endif; ?>
<?php if (!empty($help)): ?>
		<div class="row">
			<div class="col-sm-12">
				<span class="help-block"><?php echo $help; ?></span>
			</div>
		</div>
<?php endif; ?>
	</div>
</div>
