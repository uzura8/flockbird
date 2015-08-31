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

$col_sm_size = 12;
if ($optional_public_flag) $col_sm_size = 8;
?>
<div class="form-group<?php if ($val->error($name)): ?> has-error<?php endif; ?>" id="<?php echo $atter['id']; ?>_block">
	<?php echo Form::label($label, $name, array('class' => $label_class)); ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?>">
		<div class="row">
			<div class="col-sm-<?php echo $col_sm_size; ?>">

<?php if ($options): ?>
<?php $i = 0; ?>
<?php foreach ($options as $value => $label): ?>
<?php $atter['id'] = sprintf('form_%s_%s', $name, $value); ?>
<?php if ($layout_type == 'block'): ?>
				<div class="radio">
					<label>
						<?php echo Form::radio($name,$value, Input::post($name, $default_value) == $value, $atter); ?>
						<?php echo $label; ?>
					</label>
				</div>
<?php elseif ($layout_type == 'grid'): ?>
<?php if ($i % 3 == 0): ?>
				<div class="row">
<?php endif; ?>
					<div class="col-sm-4">
						<div class="radio">
							<label>
								<?php echo Form::radio($name,$value, Input::post($name, $default_value) == $value, $atter); ?>
								<?php echo $label; ?>
							</label>
						</div>
					</div>
<?php if ($i % 3 == 2): ?>
				</div>
<?php endif; ?>
<?php else: ?>
				<label class="radio-inline">
					<?php echo Form::radio($name,$value, Input::post($name, $default_value) == $value, $atter); ?>
					<?php echo $label; ?>
				</label>
<?php endif; ?>
<?php $i++; ?>
<?php endforeach; ?>
<?php if ($layout_type == 'grid' && ($i && ($i % 3 != 0))): ?>
				</div>
<?php endif; ?>
<?php endif; ?>
			</div>

<?php if ($optional_public_flag): ?>
			<div class="col-xs-12 col-sm-4 pull-right">
				<?php echo field_public_flag($optional_public_flag['value'], 'select', array(), $optional_public_flag['name']); ?>
			</div>
<?php endif; ?>

		</div>

<?php if ($val->error($name)): ?>
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
