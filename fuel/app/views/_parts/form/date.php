<?php
if ($is_required) $label .= form_required_tag();

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
$error_sm_size = 12;

$label_name = sprintf('%s_%s', $name_month, $name_day);
$block_id = sprintf('%s_%s_block', $atters['month']['id'], $atters['day']['id']);
?>
<div class="form-group<?php if ($val->error($name_month) || $val->error($name_day)): ?> has-error<?php endif; ?>" id="<?php echo $block_id; ?>">
	<?php echo Form::label($label, $label_name, array('class' => $label_class)); ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?>">
		<div class="row">
			<div class="col-sm-7">
				<div class="row">
					<div class="col-xs-5">
						<?php echo Form::select($name_month, Input::post($name_month, $def_val_month), $options['month'], $atters['month']); ?>
					</div>
					<div class="col-xs-2 text-block" style="">/</div>
					<div class="col-xs-5">
						<?php echo Form::select($name_day, Input::post($name_day, $def_val_day), $options['day'], $atters['day']); ?>
					</div>
				</div>
			</div>
<?php if ($optional_public_flag): ?>
			<div class="col-xs-12 col-sm-4 col-sm-offset-1 pull-right">
				<?php echo field_public_flag($optional_public_flag['value'], 'select', array(), $optional_public_flag['name']); ?>
			</div>
<?php endif; ?>
		</div>
<?php if ($val->error($name_month) || $val->error($name_day)): ?>
		<div class="row">
			<div class="col-sm-12">
				<ul class="help-block error_msg">
<?php if ($val->error($name_month)): ?>
					<li><?php echo $val->error($name_month)->get_message(); ?></li>
<?php endif; ?>
<?php if ($val->error($name_day)): ?>
					<li><?php echo $val->error($name_day)->get_message(); ?></li>
<?php endif; ?>
				</ul>
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
