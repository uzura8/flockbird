<?php
if ($is_required)
{
	$label .= '<span class="required">*</span>';
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
<?php if (strlen($label)): ?>
	<?php echo Form::label($label, $name, array('class' => $label_class)); ?>
<?php endif; ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?><?php if (!strlen($label) && $label_col_sm_size < 12): ?> col-sm-offset-<?php echo $label_col_sm_size; ?><?php endif; ?>">
		<div class="row">
			<div class="col-sm-<?php echo $col_sm_size; ?>">

<?php $i = 0; ?>
<?php foreach ($options as $value => $each_label): ?>
<?php
$atter['id'] = sprintf('form_%s_%s', $name, $value);
$is_checked = in_array($value, (Input::method() == 'POST') ? (array)Input::post($name) : (array)$default_value);
$each_label = $is_small_tag ? small_tag($each_label) : $each_label;
?>
<?php if ($layout_type == 'block'): ?>
				<div class="checkbox">
					<label>
						<?php echo Form::checkbox($name.'[]', $value, $is_checked, $atter); ?>
						<?php echo $each_label; ?>
					</label>
				</div>
<?php elseif ($layout_type == 'grid'): ?>
<?php if ($i % 3 == 0): ?>
				<div class="row">
<?php endif; ?>
					<div class="col-sm-4">
						<div class="checkbox">
							<label>
								<?php echo Form::checkbox($name.'[]', $value, $is_checked, $atter); ?>
								<?php echo $each_label; ?>
							</label>
						</div>
					</div>
<?php if ($i % 3 == 2): ?>
				</div>
<?php endif; ?>
<?php else: ?>
				<label class="checkbox-inline">
					<?php echo Form::checkbox($name.'[]', $value, $is_checked, $atter); ?>
					<?php echo $each_label; ?>
				</label>
<?php endif; ?>
<?php $i++; ?>
<?php endforeach; ?>
<?php if ($layout_type == 'grid' && ($i && ($i % 3 != 0))): ?>
				</div>
<?php endif; ?>
			</div>

<?php if ($optional_public_flag): ?>
			<div class="col-xs-4 pull-right">
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
<?php /*
<div class="form-group<?php if ($val->error($name)): ?> has-error<?php endif; ?>" id="<?php echo $atter['id']; ?>_block">
	<?php echo Form::label($label, $name, array('class' => $label_class)); ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?>">
		<div class="checkbox">
			<label>
<?php
$atter['id'] = sprintf('form_%s_1', $name);
if (in_array('1', Input::post($name, array()))) $atter['checked'] = 'checked';
echo Form::checkbox($name.'[]', '1', $atter);
?>
			</label>
		</div>
<?php if ($val->error($name)): ?>
		<span class="help-block error_msg"><?php echo $val->error($name)->get_message(); ?></span>
<?php endif; ?>
	</div>
</div>
*/ ?>
