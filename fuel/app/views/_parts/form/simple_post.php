<?php
if (empty($input_name)) $input_name = 'name';
$input_name_additional = !empty($additional_input_name) ? $additional_input_name : '';
$input_size = $input_name_additional ? 'input-medium' : 'input-xlarge';
$btn_attr_default = array('id' => 'btn_create');
if ($input_name != 'name') $btn_attr_default['data-input_name'] = $input_name;

if (empty($btn_attr)) $btn_attr = array();
$btn_attr = array_merge($btn_attr_default, $btn_attr);
?>
<div class="well">
<?php echo Form::open(array('class' => 'form-inline')); ?>
<div class="form-group">
	<?php echo Form::input($input_name, '', array(
		'id' => 'input_'.$input_name,
		'class' => 'form-control '.$input_size,
		'placeholder' => !empty($input_label) ? $input_label : '',
	)); ?>
</div>
<?php if ($input_name_additional): ?>
<div class="form-group">
	<?php echo Form::input($input_name_additional, '', array(
		'id' => 'input_'.$input_name_additional,
		'class' => 'form-control '.$input_size,
		'placeholder' => !empty($additional_input_label) ? $additional_input_label : '',
	)); ?>
</div>
<?php endif; ?>
<div class="form-group">
	<?php echo btn('form.do_add', null, null, true, null, null, $btn_attr, null, 'button', null, false); ?>
</div>
<?php echo Form::close(); ?>
</div><!-- well -->
