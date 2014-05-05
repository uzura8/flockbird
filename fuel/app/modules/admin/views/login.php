<div class="well">
<?php echo form_open(false, false, array('action' => Config::get('site.login_uri.admin')), array('destination' => $destination)); ?>
	<?php echo form_input($val, 'email', '', 7, 2); ?>
	<?php echo form_input($val, 'password', '', 7, 2); ?>
	<?php echo form_button(term('site.login')); ?>
<?php echo form_close(); ?>
</div>
