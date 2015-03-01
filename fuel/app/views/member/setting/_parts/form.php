<div class="well">
<?php
if (empty($label_size)) $label_size = 4;
if (!isset($form_params)) $form_params = array();
$radio_layout_type = isset($form_params['common']['radio']['layout_type']) ? $form_params['common']['radio']['layout_type'] : 'block';
?>
<?php echo form_open(); ?>
<?php $fields = $val->fieldset()->field(); ?>
<?php foreach ($fields as $name => $field_obj): ?>
<?php $type = $field_obj->get_attribute('type'); ?>
<?php if (strpos($name, 'default_public_flag')): ?>
	<?php echo form_public_flag($val, Input::post($name), false, $label_size, $name); ?>
<?php elseif ($type == 'select'): ?>
	<?php echo form_select($val, $name, Input::post($name), $label_size, $label_size); ?>
<?php elseif ($type == 'radio'): ?>
	<?php echo form_radio($val, $name, Input::post($name), $label_size, isset($form_params[$name]['layout_type']) ? $form_params[$name]['layout_type'] : $radio_layout_type); ?>
<?php elseif ($type == 'checkbox'): ?>
	<?php echo form_checkbox($val, $name, Input::post($name), $label_size); ?>
<?php endif; ?>
<?php endforeach; ?>
	<?php echo form_button('form.do_edit', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
