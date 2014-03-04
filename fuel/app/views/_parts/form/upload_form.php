<?php echo form_open(false, true, isset($form_attrs) ? $form_attrs : array()); ?>
<?php echo form_file('image'); ?>
<?php if (!empty($with_public_flag)): ?>
<?php echo form_public_flag($val); ?>
<?php endif; ?>
<?php echo form_button(); ?>
<?php echo form_close(); ?>
