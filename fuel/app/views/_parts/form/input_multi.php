<?php
if (!empty($is_required)) $common_label .= form_required_tag();

$block_attrs = array(
	'class' => 'form-group',
	'id' => sprintf('form_%s_block', implode('-', array_keys($inputs))),
);
if (!empty($has_error)) $block_attrs['class'][] = 'has-error';

$label_class = 'col-sm-'.$label_col_sm_size;
if ($label_col_sm_size != 12) $label_class .= ' control-label';
$input_col_sm_size = (12 - $label_col_sm_size) ?: 12;
$error_sm_size = 12;
?>
<div <?php echo Util_Array::conv_array2attr_string($block_attrs); ?>>
	<?php echo Form::label($common_label, null, array('class' => $label_class)); ?>
	<div class="col-sm-<?php echo $input_col_sm_size; ?>">
		<div class="row">
<?php foreach ($inputs as $name => $configs): ?>
			<div class="col-xs-<?php echo $configs['col_sm_size']; ?>">
				<?php echo Form::input($name, $configs['value'], $configs['input_attr']); ?>
			</div>
<?php endforeach; ?>
<?php if ($optional_public_flag): ?>
			<div class="col-xs-12 col-sm-4 col-sm-offset-1 pull-right">
				<?php echo field_public_flag($optional_public_flag['value'], 'select', array(), $optional_public_flag['name']); ?>
			</div>
<?php endif; ?>
		</div>
<?php if ($has_error): ?>
		<div class="row">
			<div class="col-sm-12">
				<ul class="help-block error_msg">
<?php foreach ($inputs as $name => $configs): ?>
<?php 	if ($val->error($name)): ?>
					<li><?php echo $val->error($name)->get_message(); ?></li>
<?php 	endif; ?>
<?php endforeach; ?>
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
