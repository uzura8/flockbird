<div class="well">
<?php
$form_attributes = array('action' => Config::get('site.login_uri.admin'));
?>
<?php echo form_open(false, false, $form_attributes, array('destination' => $destination)); ?>
	<?php echo form_input($val, 'email', 'Email or Username', '', true, $input_class); ?>
	<?php echo form_input($val, 'password', 'password', '', true, 'password'); ?>
	<?php echo form_button('ログイン'); ?>
<?php echo form_close(); ?>
</div>
