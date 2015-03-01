<div class="well">
<?php $label_size = 4; ?>
<?php echo form_open(); ?>
<?php $fields = $val->fieldset()->field(); ?>
<?php foreach ($fields as $name => $field_obj): ?>
<?php $type = $field_obj->get_attribute('type'); ?>
<?php if (strpos($name, 'default_public_flag')): ?>
	<?php echo form_public_flag($val, Input::post($name), false, $label_size, $name); ?>
<?php elseif ($type == 'select'): ?>
	<?php echo form_select($val, $name, Input::post($name), 6, $label_size); ?>
<?php elseif ($type == 'radio'): ?>
	<?php echo form_radio($val, $name, Input::post($name), $label_size, 'inline'); ?>
<?php elseif ($type == 'checkbox'): ?>
	<?php echo form_checkbox($val, $name, Input::post($name), $label_size, 'inline'); ?>
<?php endif; ?>
<?php endforeach; ?>
	<?php echo form_button('form.do_edit', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
