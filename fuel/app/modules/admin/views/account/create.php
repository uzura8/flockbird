<div class="well">
<?php $label_size = 3; ?>
<?php echo form_open(true); ?>
	<?php echo form_input($val, 'username', '', 7, $label_size); ?>
	<?php echo form_input($val, 'email', '', 7, $label_size); ?>
	<?php echo form_input($val, 'password', '', 7, $label_size); ?>
	<?php echo form_select($val, 'group', 1, 7, $label_size); ?>
	<?php echo form_button('form.do_create', 'submit', '', null, $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->
