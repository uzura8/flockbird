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
$error_sm_size = 12;

$locations = array(Input::post($names[0], $default_values[0]), Input::post($names[1], $default_values[1]));
if ($val->error($names[0]) || $val->error($names[1])) $locations = array(null, null);
?>
<div class="form-group<?php if ($val->error($names[0]) || $val->error($names[1])): ?> has-error<?php endif; ?>" id="form_location_block">
	<?php echo Form::label($label, 'location', array('class' => $label_class)); ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?>">
		<div class="row">
			<div class="col-sm-12">
				<?php echo render('_parts/map/detail', array(
					'locations' => $locations,
					'markers' => ($locations[0] && $locations[1]) ? Site_Util::get_map_markers($locations) : array(),
					'is_form_view' => true,
				)); ?>
			</div>
		</div>
<?php if ($val->error($names[0]) || $val->error($names[1])): ?>
		<div class="row">
			<div class="col-sm-12">
				<span class="help-block error_msg"><?php echo $val->error($names[0])->get_message(); ?></span>
				<span class="help-block error_msg"><?php echo $val->error($names[1])->get_message(); ?></span>
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
	<?php echo Form::hidden($names[0], Input::post($names[0], $default_values[0]), $input_atter_lat); ?>
	<?php echo Form::hidden($names[1], Input::post($names[1], $default_values[1]), $input_atter_lng); ?>
*/ ?>
