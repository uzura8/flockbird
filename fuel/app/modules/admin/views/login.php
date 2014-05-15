<div class="well">
<?php echo form_open(false, false, array('action' => conf('login_uri.admin')), array('destination' => $destination)); ?>
	<?php echo form_input($val, 'email', '', 7, 2); ?>
	<?php echo form_input($val, 'password', '', 7, 2); ?>
	<?php echo form_button('site.login'); ?>
<?php echo form_close(); ?>
</div>
