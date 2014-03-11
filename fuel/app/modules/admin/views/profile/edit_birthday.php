<div class="well">
<?php $label_size = 4; ?>
<?php echo form_open(); ?>
<?php $fields = $val->fieldset()->field(); ?>
<?php foreach ($fields as $name => $field_obj): ?>
<?php $type = $field_obj->get_attribute('type'); ?>
<?php if (in_array($name, array('profile_birthday_default_public_flag_birthyear', 'profile_birthday_default_public_flag_birthday'))): ?>
	<?php echo form_public_flag($val, Input::post($name), false, $label_size, false, $name); ?>
<?php elseif ($type == 'select'): ?>
	<?php echo form_select($val, $name, Input::post($name), 6, $label_size); ?>
<?php elseif ($type == 'radio'): ?>
	<?php echo form_radio($val, $name, Input::post($name), $label_size, 'inline'); ?>
<?php endif; ?>
<?php endforeach; ?>
	<?php echo form_button('編集する', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
